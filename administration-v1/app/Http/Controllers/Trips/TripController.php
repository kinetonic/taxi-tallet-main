<?php

namespace App\Http\Controllers\Trips;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['rider', 'driver']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('trip_id', 'like', "%{$search}%")
                  ->orWhere('pickup_address', 'like', "%{$search}%")
                  ->orWhere('dropoff_address', 'like', "%{$search}%")
                  ->orWhereHas('rider', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('driver', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by vehicle type
        if ($request->has('vehicle_type') && $request->vehicle_type !== 'all') {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        // Sort
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $trips = $query->paginate($perPage);

        // Statistics
        $stats = [
            'total' => Trip::count(),
            'pending' => Trip::where('status', 'pending')->count(),
            'searching' => Trip::where('status', 'searching')->count(),
            'driver_assigned' => Trip::where('status', 'driver_assigned')->count(),
            'started' => Trip::where('status', 'started')->count(),
            'completed' => Trip::where('status', 'completed')->count(),
            'cancelled' => Trip::where('status', 'cancelled')->count(),
            'revenue' => Trip::where('status', 'completed')->sum('final_price'),
            'today' => Trip::whereDate('created_at', today())->count(),
            'this_month' => Trip::whereMonth('created_at', now()->month)->count(),
        ];

        // Get filter options
        $statuses = [
            'all' => 'Tous les statuts',
            'pending' => 'En attente',
            'searching' => 'Recherche chauffeur',
            'driver_assigned' => 'Chauffeur assigné',
            'driver_arrived' => 'Chauffeur arrivé',
            'started' => 'Course démarrée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
        ];

        $paymentMethods = [
            'all' => 'Tous les paiements',
            'cash' => 'Espèces',
            'card' => 'Carte',
            'wallet' => 'Portefeuille',
            'mobile_money' => 'Mobile Money',
        ];

        $vehicleTypes = [
            'all' => 'Tous les véhicules',
            'economy' => 'Économique',
            'comfort' => 'Confort',
            'premium' => 'Premium',
            'van' => 'Van',
            'bike' => 'Moto',
        ];

        return view('trips.index', compact(
            'trips', 
            'stats', 
            'statuses',
            'paymentMethods',
            'vehicleTypes'
        ));
    }

    public function show(Trip $trip)
    {
        $trip->load(['rider', 'driver']);
        
        // Get trip timeline
        $timeline = $this->getTripTimeline($trip);
        
        // Get similar trips
        $similarTrips = Trip::where('rider_id', $trip->rider_id)
            ->orWhere('driver_id', $trip->driver_id)
            ->where('id', '!=', $trip->id)
            ->take(5)
            ->get();

        return view('trips.show', compact('trip', 'timeline', 'similarTrips'));
    }

    public function edit(Trip $trip)
    {
        $trip->load(['rider', 'driver']);
        
        // Get available drivers
        $availableDrivers = User::where('type', 'driver')
            ->where('is_active', true)
            ->where('is_online', true)
            ->where('driver_status', 'available')
            ->get();

        // Get status options
        $statusOptions = [
            'pending' => 'En attente',
            'searching' => 'Recherche chauffeur',
            'driver_assigned' => 'Chauffeur assigné',
            'driver_arrived' => 'Chauffeur arrivé',
            'started' => 'Course démarrée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
        ];

        // Get payment status options
        $paymentStatusOptions = [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'refunded' => 'Remboursé',
        ];

        return view('trips.edit', compact(
            'trip', 
            'availableDrivers', 
            'statusOptions',
            'paymentStatusOptions'
        ));
    }

    public function update(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,searching,driver_assigned,driver_arrived,started,completed,cancelled',
            'driver_id' => 'nullable|exists:users,id',
            'final_price' => 'nullable|numeric|min:0',
            'driver_earnings' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $trip->status;
            $newStatus = $validated['status'];

            // Update trip
            $trip->update($validated);

            // If status changed to completed and has final price
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                if (!$trip->completed_at) {
                    $trip->update(['completed_at' => now()]);
                }

                // If driver exists, update their stats
                if ($trip->driver_id) {
                    $driver = $trip->driver;
                    $driver->increment('total_trips');
                    
                    if ($trip->driver_earnings) {
                        $driver->increment('earnings', $trip->driver_earnings);
                    }
                }

                // Process payment if marked as paid
                if ($validated['payment_status'] === 'paid') {
                    $trip->update(['paid_at' => now()]);
                }
            }

            // If status changed to cancelled
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $trip->update([
                    'cancelled_at' => now(),
                    'cancelled_by' => 'admin',
                    'cancellation_reason' => $validated['admin_notes'] ?? 'Annulé par l\'administrateur',
                ]);

                // Release any held wallet amount
                if ($trip->payment_method === 'wallet' && $trip->rider->wallet) {
                    $trip->rider->wallet->release(
                        $trip->estimated_price,
                        "Release cancelled trip by admin: {$trip->trip_id}"
                    );
                }

                // If driver was assigned, make them available
                if ($trip->driver_id) {
                    $trip->driver->update([
                        'driver_status' => 'available',
                        'is_online' => true,
                    ]);
                }
            }

            // If driver changed
            if (isset($validated['driver_id']) && $validated['driver_id'] != $trip->driver_id) {
                // Make old driver available if exists
                if ($trip->driver_id) {
                    $oldDriver = User::find($trip->driver_id);
                    if ($oldDriver) {
                        $oldDriver->update([
                            'driver_status' => 'available',
                            'is_online' => true,
                        ]);
                    }
                }

                // Update new driver status
                if ($validated['driver_id']) {
                    $newDriver = User::find($validated['driver_id']);
                    if ($newDriver && $newStatus !== 'completed' && $newStatus !== 'cancelled') {
                        $newDriver->update([
                            'driver_status' => 'on_ride',
                            'is_online' => false,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('trips.show', $trip)
                ->with('success', 'Course mise à jour avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Trip $trip)
    {
        // Only allow deletion of certain statuses
        if (!in_array($trip->status, ['cancelled', 'completed'])) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer une course active');
        }

        try {
            $trip->delete();
            return redirect()->route('trips.index')
                ->with('success', 'Course supprimée avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $query = Trip::with(['rider', 'driver']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $trips = $query->get();

        // Generate CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=trips_' . date('Y-m-d') . '.csv',
        ];

        $callback = function() use ($trips) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Headers
            fputcsv($file, [
                'ID Course',
                'Client',
                'Téléphone Client',
                'Chauffeur',
                'Téléphone Chauffeur',
                'Adresse départ',
                'Adresse arrivée',
                'Distance (km)',
                'Durée (min)',
                'Type véhicule',
                'Prix estimé',
                'Prix final',
                'Gains chauffeur',
                'Méthode paiement',
                'Statut paiement',
                'Statut course',
                'Date création',
                'Date début',
                'Date fin',
                'Note chauffeur',
                'Note client'
            ], ';');

            // Data
            foreach ($trips as $trip) {
                fputcsv($file, [
                    $trip->trip_id,
                    $trip->rider ? $trip->rider->full_name : 'N/A',
                    $trip->rider ? $trip->rider->phone : 'N/A',
                    $trip->driver ? $trip->driver->full_name : 'N/A',
                    $trip->driver ? $trip->driver->phone : 'N/A',
                    $trip->pickup_address,
                    $trip->dropoff_address,
                    $trip->distance,
                    $trip->duration,
                    $this->getVehicleTypeLabel($trip->vehicle_type),
                    $trip->estimated_price,
                    $trip->final_price,
                    $trip->driver_earnings,
                    $this->getPaymentMethodLabel($trip->payment_method),
                    $this->getPaymentStatusLabel($trip->payment_status),
                    $this->getStatusLabel($trip->status),
                    $trip->created_at,
                    $trip->started_at,
                    $trip->completed_at,
                    $trip->driver_rating,
                    $trip->rider_rating
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }



    public function singleMap(Trip $trip)
    {
        $trip->load(['rider', 'driver']);
        
        // Check if we have coordinates
        if (!$trip->pickup_latitude || !$trip->pickup_longitude || 
            !$trip->dropoff_latitude || !$trip->dropoff_longitude) {
            return redirect()->route('trips.show', $trip)
                ->with('error', 'Les coordonnées GPS ne sont pas disponibles pour cette course.');
        }
        
        return view('trips.map-single', compact('trip'));
    }

    public function tripMap()
    {
        // Get active trips for map
        $activeTrips = Trip::with(['rider', 'driver'])
            ->whereIn('status', ['searching', 'driver_assigned', 'driver_arrived', 'started'])
            ->get();

        // Get available drivers
        $availableDrivers = User::where('type', 'driver')
            ->where('is_online', true)
            ->where('driver_status', 'available')
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get();

        // Get completed trips for last 24 hours (for heat map)
        $recentTrips = Trip::where('status', 'completed')
            ->where('completed_at', '>=', now()->subHours(24))
            ->whereNotNull('pickup_latitude')
            ->whereNotNull('pickup_longitude')
            ->select('pickup_latitude', 'pickup_longitude', 'dropoff_latitude', 'dropoff_longitude')
            ->get();

        // Get map center coordinates (use first active trip or default)
        $centerLat = 36.8065; // Tunisia default
        $centerLng = 10.1815;
        
        if ($activeTrips->count() > 0) {
            $centerLat = $activeTrips->first()->pickup_latitude ?? $centerLat;
            $centerLng = $activeTrips->first()->pickup_longitude ?? $centerLng;
        }

        return view('trips.map', compact(
            'activeTrips', 
            'availableDrivers', 
            'recentTrips',
            'centerLat',
            'centerLng'
        ));
    }

    public function analytics()
    {
        // Daily trips for last 30 days
        $dailyTrips = Trip::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by day
        $dailyRevenue = Trip::selectRaw('DATE(created_at) as date, SUM(final_price) as revenue')
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Trips by status
        $tripsByStatus = Trip::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Trips by vehicle type
        $tripsByVehicle = Trip::selectRaw('vehicle_type, COUNT(*) as count')
            ->groupBy('vehicle_type')
            ->get();

        // Top drivers by earnings
        $topDrivers = User::where('type', 'driver')
            ->whereHas('tripsAsDriver', function($query) {
                $query->where('status', 'completed');
            })
            ->withCount(['tripsAsDriver as completed_trips' => function($query) {
                $query->where('status', 'completed');
            }])
            ->withSum(['tripsAsDriver as total_earnings' => function($query) {
                $query->where('status', 'completed');
            }], 'driver_earnings')
            ->orderBy('total_earnings', 'desc')
            ->take(10)
            ->get();

        // Top riders by trips
        $topRiders = User::where('type', 'rider')
            ->withCount(['tripsAsRider as completed_trips' => function($query) {
                $query->where('status', 'completed');
            }])
            ->orderBy('completed_trips', 'desc')
            ->take(10)
            ->get();

        return view('trips.analytics', compact(
            'dailyTrips',
            'dailyRevenue',
            'tripsByStatus',
            'tripsByVehicle',
            'topDrivers',
            'topRiders'
        ));
    }

    private function getTripTimeline(Trip $trip)
    {
        $timeline = [];

        if ($trip->created_at) {
            $timeline[] = [
                'time' => $trip->created_at,
                'title' => 'Course créée',
                'description' => 'La course a été demandée par le client',
                'icon' => 'plus',
                'color' => 'blue',
            ];
        }

        if ($trip->requested_at) {
            $timeline[] = [
                'time' => $trip->requested_at,
                'title' => 'Demande confirmée',
                'description' => 'La recherche de chauffeur a commencé',
                'icon' => 'search',
                'color' => 'yellow',
            ];
        }

        if ($trip->searching_at) {
            $timeline[] = [
                'time' => $trip->searching_at,
                'title' => 'Recherche chauffeur',
                'description' => "Recherche de chauffeur ({$trip->driver_search_count} disponibles)",
                'icon' => 'user-friends',
                'color' => 'orange',
            ];
        }

        if ($trip->driver_assigned_at) {
            $timeline[] = [
                'time' => $trip->driver_assigned_at,
                'title' => 'Chauffeur assigné',
                'description' => $trip->driver ? "Chauffeur: {$trip->driver->full_name}" : 'Chauffeur assigné',
                'icon' => 'user-check',
                'color' => 'green',
            ];
        }

        if ($trip->driver_arrived_at) {
            $timeline[] = [
                'time' => $trip->driver_arrived_at,
                'title' => 'Chauffeur arrivé',
                'description' => 'Le chauffeur est arrivé au point de prise en charge',
                'icon' => 'map-marker-alt',
                'color' => 'teal',
            ];
        }

        if ($trip->started_at) {
            $timeline[] = [
                'time' => $trip->started_at,
                'title' => 'Course démarrée',
                'description' => 'La course a commencé',
                'icon' => 'play',
                'color' => 'indigo',
            ];
        }

        if ($trip->completed_at) {
            $timeline[] = [
                'time' => $trip->completed_at,
                'title' => 'Course terminée',
                'description' => "Course terminée - Prix: {$trip->final_price} TND",
                'icon' => 'check-circle',
                'color' => 'purple',
            ];
        }

        if ($trip->paid_at) {
            $timeline[] = [
                'time' => $trip->paid_at,
                'title' => 'Paiement effectué',
                'description' => "Paiement {$trip->payment_method} effectué",
                'icon' => 'credit-card',
                'color' => 'green',
            ];
        }

        if ($trip->cancelled_at) {
            $timeline[] = [
                'time' => $trip->cancelled_at,
                'title' => 'Course annulée',
                'description' => $trip->cancellation_reason ? "Annulée par {$trip->cancelled_by}: {$trip->cancellation_reason}" : 'Course annulée',
                'icon' => 'times-circle',
                'color' => 'red',
            ];
        }

        // Sort by time
        usort($timeline, function($a, $b) {
            return $a['time'] <=> $b['time'];
        });

        return $timeline;
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'En attente',
            'searching' => 'Recherche chauffeur',
            'driver_assigned' => 'Chauffeur assigné',
            'driver_arrived' => 'Chauffeur arrivé',
            'started' => 'Course démarrée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
        ];

        return $labels[$status] ?? $status;
    }

    private function getPaymentMethodLabel($method)
    {
        $labels = [
            'cash' => 'Espèces',
            'card' => 'Carte',
            'wallet' => 'Portefeuille',
            'mobile_money' => 'Mobile Money',
        ];

        return $labels[$method] ?? $method;
    }

    private function getPaymentStatusLabel($status)
    {
        $labels = [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'refunded' => 'Remboursé',
        ];

        return $labels[$status] ?? $status;
    }

    private function getVehicleTypeLabel($type)
    {
        $labels = [
            'economy' => 'Économique',
            'comfort' => 'Confort',
            'premium' => 'Premium',
            'van' => 'Van',
            'bike' => 'Moto',
        ];

        return $labels[$type] ?? $type;
    }
}