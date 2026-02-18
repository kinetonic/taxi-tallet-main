<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DriversController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get statistics for the dashboard
        $stats = [
            'drivers' => User::where('type', 'driver')->get(),
            'activeDrivers' => User::where('type', 'driver')->where('is_active', true)->count(),
            'onlineDrivers' => User::where('type', 'driver')->where('is_online', true)->count(),
            'onTripDrivers' => User::where('type', 'driver')->where('driver_status', 'on_trip')->count(),
            'sortField' => 'first_name',
            'sortDirection' => 'asc',
        ];


        //die(var_dump($stats));

        return view('drivers.index', $stats);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('drivers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
            'driver_license_number' => 'required|string|max:50',
            'driver_license_expiry' => 'required|date|after:today',
            'vehicle_type' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'vehicle_plate_number' => 'required|string|max:20|unique:users,vehicle_plate_number',
            'vehicle_color' => 'required|string|max:50',
            'date_of_birth' => 'required|date|before:-18 years',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars/drivers', 'public');
            $validated['avatar'] = $avatarPath;
        }

        // Create driver
        $driver = User::create([
            'type' => 'driver',
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'driver_license_number' => $validated['driver_license_number'],
            'driver_license_expiry' => $validated['driver_license_expiry'],
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_model' => $validated['vehicle_model'],
            'vehicle_year' => $validated['vehicle_year'],
            'vehicle_plate_number' => $validated['vehicle_plate_number'],
            'vehicle_color' => $validated['vehicle_color'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'postal_code' => $validated['postal_code'],
            'emergency_contact_name' => $validated['emergency_contact_name'],
            'emergency_contact_phone' => $validated['emergency_contact_phone'],
            'avatar' => $validated['avatar'] ?? null,
            'driver_status' => 'available',
            'is_online' => false,
            'is_active' => true,
            'is_verified' => true,
            'rating' => 5.0,
            'total_trips' => 0,
            'earnings' => 0.00,
        ]);

        return redirect()->route('chauffeurs.index')
            ->with('success', 'Chauffeur créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $driver)
    {
        // Ensure the user is a driver
        if ($driver->type !== 'driver') {
            abort(404, 'Chauffeur non trouvé.');
        }

        $driver->load(['driverTrips' => function($query) {
            $query->latest()->take(10);
        }, 'driverDocuments']);

        $stats = [
            'total_trips' => $driver->driverTrips()->count(),
            'completed_trips' => $driver->driverTrips()->where('status', 'completed')->count(),
            'cancelled_trips' => $driver->driverTrips()->where('status', 'cancelled')->count(),
            'total_earnings' => $driver->driverTrips()->where('status', 'completed')->sum('fare_amount'),
            'average_rating' => $driver->driverTrips()->where('status', 'completed')->avg('rating') ?? 0,
        ];

        return view('drivers.show', compact('driver', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $driver)
    {
        if ($driver->type !== 'driver') {
            abort(404, 'Chauffeur non trouvé.');
        }

        return view('drivers.edit', compact('driver'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $driver)
    {
        if ($driver->type !== 'driver') {
            abort(404, 'Chauffeur non trouvé.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($driver->id)
            ],
            'phone' => 'required|string|max:20',
            'password' => 'nullable|min:8|confirmed',
            'driver_license_number' => 'required|string|max:50',
            'driver_license_expiry' => 'required|date|after:today',
            'vehicle_type' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'vehicle_plate_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users')->ignore($driver->id)
            ],
            'vehicle_color' => 'required|string|max:50',
            'date_of_birth' => 'required|date|before:-18 years',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'driver_status' => 'required|in:available,on_trip,offline,maintenance',
            'is_online' => 'boolean',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($driver->avatar) {
                Storage::disk('public')->delete($driver->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars/drivers', 'public');
            $validated['avatar'] = $avatarPath;
        }

        // Update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $driver->update($validated);

        return redirect()->route('chauffeurs.show', $driver)
            ->with('success', 'Chauffeur mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $driver)
    {
        if ($driver->type !== 'driver') {
            abort(404, 'Chauffeur non trouvé.');
        }

        // Delete avatar if exists
        if ($driver->avatar) {
            Storage::disk('public')->delete($driver->avatar);
        }

        $driver->delete();

        return redirect()->route('chauffeurs.index')
            ->with('success', 'Chauffeur supprimé avec succès.');
    }

    /**
     * Toggle driver active status
     */
    public function toggleStatus(User $driver)
    {
        if ($driver->type !== 'driver') {
            abort(404, 'Chauffeur non trouvé.');
        }

        $driver->update([
            'is_active' => !$driver->is_active
        ]);

        $status = $driver->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Chauffeur {$status} avec succès.");
    }

    /**
     * Toggle driver online status
     */
    public function toggleOnline(User $driver)
    {
        if ($driver->type !== 'driver') {
            abort(404, 'Chauffeur non trouvé.');
        }

        $driver->update([
            'is_online' => !$driver->is_online,
            'last_online_at' => now()
        ]);

        $status = $driver->is_online ? 'en ligne' : 'hors ligne';

        return back()->with('success', "Chauffeur mis {$status} avec succès.");
    }

    /**
     * Update driver status
     */
    public function updateStatus(Request $request, User $driver)
    {
        if ($driver->type !== 'driver') {
            abort(404, 'Chauffeur non trouvé.');
        }

        $request->validate([
            'status' => 'required|in:available,on_trip,offline,maintenance'
        ]);

        $driver->update([
            'driver_status' => $request->status
        ]);

        $statusLabels = [
            'available' => 'disponible',
            'on_trip' => 'en course',
            'offline' => 'hors ligne',
            'maintenance' => 'en maintenance'
        ];

        return back()->with('success', "Statut du chauffeur mis à jour: {$statusLabels[$request->status]}.");
    }

    /**
     * Show driver documents
     */
    public function documents(User $driver)
    {
        if ($driver->type !== 'driver') {
            abort(404, 'Chauffeur non trouvé.');
        }

        $driver->load('driverDocuments');

        return view('drivers.documents', compact('driver'));
    }

    /**
     * Show driver trips
     */
    public function trips(User $driver)
    {
        if ($driver->type !== 'driver') {
            abort(404, 'Chauffeur non trouvé.');
        }

        $trips = $driver->driverTrips()
            ->with(['rider', 'payment'])
            ->latest()
            ->paginate(15);

        return view('drivers.trips', compact('driver', 'trips'));
    }

    /**
     * Show driver analytics
     */
    public function analytics(User $driver)
    {
        if ($driver->type !== 'driver') {
            abort(404, 'Chauffeur non trouvé.');
        }

        $analytics = [
            'weekly_trips' => $this->getWeeklyTrips($driver),
            'monthly_earnings' => $this->getMonthlyEarnings($driver),
            'rating_distribution' => $this->getRatingDistribution($driver),
            'peak_hours' => $this->getPeakHours($driver),
        ];

        return view('drivers.analytics', compact('driver', 'analytics'));
    }

    /**
     * Get weekly trips data for analytics
     */
    private function getWeeklyTrips(User $driver)
    {
        return $driver->driverTrips()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get monthly earnings data for analytics
     */
    private function getMonthlyEarnings(User $driver)
    {
        return $driver->driverTrips()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(fare_amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get rating distribution for analytics
     */
    private function getRatingDistribution(User $driver)
    {
        return $driver->driverTrips()
            ->where('status', 'completed')
            ->whereNotNull('rating')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();
    }

    /**
     * Get peak hours for analytics
     */
    private function getPeakHours(User $driver)
    {
        return $driver->driverTrips()
            ->where('status', 'completed')
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * Bulk actions for drivers
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'drivers' => 'required|array',
            'drivers.*' => 'exists:users,id,type,driver'
        ]);

        $driverIds = $request->drivers;

        switch ($request->action) {
            case 'activate':
                User::whereIn('id', $driverIds)->update(['is_active' => true]);
                $message = 'Chauffeurs activés avec succès.';
                break;

            case 'deactivate':
                User::whereIn('id', $driverIds)->update(['is_active' => false]);
                $message = 'Chauffeurs désactivés avec succès.';
                break;

            case 'delete':
                User::whereIn('id', $driverIds)->delete();
                $message = 'Chauffeurs supprimés avec succès.';
                break;
        }

        return back()->with('success', $message);
    }
}