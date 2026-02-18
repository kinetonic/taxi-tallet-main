<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get statistics for the dashboard
        $stats = [
            'riders' => User::where('type', 'rider')->get(),
            'activeRiders' => User::where('type', 'rider')->where('is_active', true)->count(),
            'totalRiders' => User::where('type', 'rider')->count(),
            'sortField' => 'first_name',
            'sortDirection' => 'asc',
        ];

        return view('clients.index', $stats);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
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
            'date_of_birth' => 'required|date|before:-18 years',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'preferred_payment_method' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars/riders', 'public');
            $validated['avatar'] = $avatarPath;
        }

        // Create rider
        $rider = User::create([
            'type' => 'rider',
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'postal_code' => $validated['postal_code'],
            'emergency_contact_name' => $validated['emergency_contact_name'],
            'emergency_contact_phone' => $validated['emergency_contact_phone'],
            'preferred_payment_method' => $validated['preferred_payment_method'] ?? null,
            'avatar' => $validated['avatar'] ?? null,
            'rider_status' => 'active',
            'is_active' => true,
            'is_verified' => true,
            'rider_rating' => 5.0,
            'rider_trips_count' => 0,
        ]);

        return redirect()->route('clients.index')
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $client)
    {
        // Ensure the user is a rider
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        $client->load(['riderTrips' => function($query) {
            $query->latest()->take(10);
        }, 'paymentMethods']);

        $stats = [
            'total_trips' => $client->riderTrips()->count(),
            'completed_trips' => $client->riderTrips()->where('status', 'completed')->count(),
            'cancelled_trips' => $client->riderTrips()->where('status', 'cancelled')->count(),
            'total_spent' => $client->riderTrips()->where('status', 'completed')->sum('fare_amount'),
            'average_rating' => $client->rider_rating,
        ];

        return view('clients.show', compact('client', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $client)
    {
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $client)
    {
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($client->id)
            ],
            'phone' => 'required|string|max:20',
            'password' => 'nullable|min:8|confirmed',
            'date_of_birth' => 'required|date|before:-18 years',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'preferred_payment_method' => 'nullable|string|max:50',
            'rider_status' => 'required|in:active,inactive,suspended',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($client->avatar) {
                Storage::disk('public')->delete($client->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars/riders', 'public');
            $validated['avatar'] = $avatarPath;
        }

        // Update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $client)
    {
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        // Delete avatar if exists
        if ($client->avatar) {
            Storage::disk('public')->delete($client->avatar);
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }

    /**
     * Toggle rider active status
     */
    public function toggleStatus(User $client)
    {
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        $client->update([
            'is_active' => !$client->is_active
        ]);

        $status = $client->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Client {$status} avec succès.");
    }

    /**
     * Update rider status
     */
    public function updateStatus(Request $request, User $client)
    {
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        $request->validate([
            'status' => 'required|in:active,inactive,suspended'
        ]);

        $client->update([
            'rider_status' => $request->status
        ]);

        $statusLabels = [
            'active' => 'actif',
            'inactive' => 'inactif',
            'suspended' => 'suspendu'
        ];

        return back()->with('success', "Statut du client mis à jour: {$statusLabels[$request->status]}.");
    }

    /**
     * Show rider payment methods
     */
    public function paymentMethods(User $client)
    {
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        $client->load('paymentMethods');

        return view('clients.payment-methods', compact('client'));
    }

    /**
     * Show rider trips
     */
    public function trips(User $client)
    {
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        $trips = $client->riderTrips()
            ->with(['driver', 'payment'])
            ->latest()
            ->paginate(15);

        return view('clients.trips', compact('client', 'trips'));
    }

    /**
     * Show rider analytics
     */
    public function analytics(User $client)
    {
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        $analytics = [
            'weekly_trips' => $this->getWeeklyTrips($client),
            'monthly_spending' => $this->getMonthlySpending($client),
            'trip_distribution' => $this->getTripDistribution($client),
            'preferred_hours' => $this->getPreferredHours($client),
        ];

        return view('clients.analytics', compact('client', 'analytics'));
    }

    /**
     * Get weekly trips data for analytics
     */
    private function getWeeklyTrips(User $client)
    {
        return $client->riderTrips()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get monthly spending data for analytics
     */
    private function getMonthlySpending(User $client)
    {
        return $client->riderTrips()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(fare_amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get trip distribution for analytics
     */
    private function getTripDistribution(User $client)
    {
        return $client->riderTrips()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
    }

    /**
     * Get preferred hours for analytics
     */
    private function getPreferredHours(User $client)
    {
        return $client->riderTrips()
            ->where('status', 'completed')
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * Bulk actions for riders
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'clients' => 'required|array',
            'clients.*' => 'exists:users,id,type,rider'
        ]);

        $clientIds = $request->clients;

        switch ($request->action) {
            case 'activate':
                User::whereIn('id', $clientIds)->update(['is_active' => true]);
                $message = 'Clients activés avec succès.';
                break;

            case 'deactivate':
                User::whereIn('id', $clientIds)->update(['is_active' => false]);
                $message = 'Clients désactivés avec succès.';
                break;

            case 'delete':
                User::whereIn('id', $clientIds)->delete();
                $message = 'Clients supprimés avec succès.';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Show rider preferences
     */
    public function preferences(User $client)
    {
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        return view('clients.preferences', compact('client'));
    }

    /**
     * Update rider preferences
     */
    public function updatePreferences(Request $request, User $client)
    {
        if ($client->type !== 'rider') {
            abort(404, 'Client non trouvé.');
        }

        $validated = $request->validate([
            'preferences.notifications' => 'boolean',
            'preferences.sms_notifications' => 'boolean',
            'preferences.email_notifications' => 'boolean',
            'preferences.preferred_vehicle_type' => 'nullable|string|max:50',
            'preferences.language' => 'nullable|string|max:10',
            'preferences.currency' => 'nullable|string|max:3',
        ]);

        $client->update([
            'preferences' => array_merge((array) $client->preferences, $validated['preferences'])
        ]);

        return back()->with('success', 'Préférences mises à jour avec succès.');
    }
}