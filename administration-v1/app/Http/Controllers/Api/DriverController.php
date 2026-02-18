<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DriverDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    // Get driver profile
    public function getProfile(Request $request)
    {
        $driver = $request->user();
        
        if (!$driver->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a driver'
            ], 403);
        }

        $driver->load(['driverDocuments', 'wallet']);

        return response()->json([
            'success' => true,
            'message' => 'Driver profile retrieved',
            'data' => $driver
        ]);
    }

    // Update driver profile
    public function updateProfile(Request $request)
    {
        $driver = $request->user();
        
        if (!$driver->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a driver'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:50',
            'last_name' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|unique:users,email,' . $driver->id,
            'phone' => 'sometimes|string|unique:users,phone,' . $driver->id,
            'vehicle_type' => 'sometimes|string|max:50',
            'vehicle_model' => 'sometimes|string|max:100',
            'vehicle_year' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_color' => 'sometimes|string|max:30',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'country' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'emergency_contact_name' => 'sometimes|string|max:100',
            'emergency_contact_phone' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $driver->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $driver
        ]);
    }

    // Update driver status
    public function updateStatus(Request $request)
    {
        $driver = $request->user();
        
        if (!$driver->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a driver'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,offline,busy',
            'is_online' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        
        // Update last_online_at if going online/offline
        if (isset($data['is_online'])) {
            $data['last_online_at'] = now();
        }

        $driver->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => [
                'driver_status' => $driver->driver_status,
                'is_online' => $driver->is_online,
                'last_online_at' => $driver->last_online_at,
            ]
        ]);
    }

    // Upload driver document
    public function uploadDocument(Request $request)
    {
        $driver = $request->user();
        
        if (!$driver->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a driver'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:license_front,license_back,vehicle_registration,insurance,vehicle_front,vehicle_back,vehicle_left,vehicle_right,profile_photo',
            'document' => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120', // 5MB max
            'document_number' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('document');
            
            // Generate unique filename
            $fileName = 'driver_' . $driver->id . '_' . time() . '_' . $request->document_type . '.' . $file->getClientOriginalExtension();
            
            // Store in storage/app/public/driver-documents
            $path = $file->storeAs('driver-documents', $fileName, 'public');
            
            // Create document record
            $document = DriverDocument::create([
                'driver_id' => $driver->id,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'expiry_date' => $request->expiry_date,
                'is_expired' => $request->expiry_date ? now()->greaterThan($request->expiry_date) : false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get driver documents
    public function getDocuments(Request $request)
    {
        $driver = $request->user();
        
        if (!$driver->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a driver'
            ], 403);
        }

        $documents = $driver->driverDocuments()->get();

        return response()->json([
            'success' => true,
            'message' => 'Driver documents retrieved',
            'data' => $documents
        ]);
    }

    // Get driver statistics
    public function getStatistics(Request $request)
    {
        $driver = $request->user();
        
        if (!$driver->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a driver'
            ], 403);
        }

        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        // Get trips data
        $tripsQuery = $driver->driverTrips();
        
        $stats = [
            'total_trips' => $driver->total_trips,
            'total_earnings' => (float) $driver->earnings,
            'average_rating' => (float) $driver->rating,
            
            'today' => [
                'trips' => $tripsQuery->whereDate('created_at', today())->count(),
                'earnings' => (float) $tripsQuery->whereDate('created_at', today())
                    ->where('status', 'completed')
                    ->sum('driver_earnings'),
            ],
            
            'this_week' => [
                'trips' => $tripsQuery->where('created_at', '>=', $weekStart)->count(),
                'earnings' => (float) $tripsQuery->where('created_at', '>=', $weekStart)
                    ->where('status', 'completed')
                    ->sum('driver_earnings'),
            ],
            
            'this_month' => [
                'trips' => $tripsQuery->where('created_at', '>=', $monthStart)->count(),
                'earnings' => (float) $tripsQuery->where('created_at', '>=', $monthStart)
                    ->where('status', 'completed')
                    ->sum('driver_earnings'),
            ],
            
            'recent_trips' => $driver->driverTrips()
                ->where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->limit(10)
                ->get(['trip_id', 'completed_at', 'final_price', 'driver_earnings', 'rider_rating']),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Driver statistics retrieved',
            'data' => $stats
        ]);
    }

    // Get driver earnings breakdown
    public function getEarnings(Request $request)
    {
        $driver = $request->user();
        
        if (!$driver->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a driver'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'period' => 'sometimes|in:day,week,month,year',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $period = $request->period ?? 'month';
        $startDate = $request->start_date ?: now()->startOfMonth();
        $endDate = $request->end_date ?: now()->endOfMonth();

        $trips = $driver->driverTrips()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->orderBy('completed_at')
            ->get(['completed_at', 'driver_earnings', 'final_price']);

        $totalEarnings = $trips->sum('driver_earnings');
        $totalRides = $trips->count();
        $averageEarningsPerRide = $totalRides > 0 ? $totalEarnings / $totalRides : 0;

        // Group by period
        $groupedEarnings = [];
        
        foreach ($trips as $trip) {
            $key = match($period) {
                'day' => $trip->completed_at->format('Y-m-d H:00'),
                'week' => $trip->completed_at->format('Y-W'),
                'year' => $trip->completed_at->format('Y'),
                default => $trip->completed_at->format('Y-m-d'),
            };
            
            if (!isset($groupedEarnings[$key])) {
                $groupedEarnings[$key] = [
                    'period' => $key,
                    'earnings' => 0,
                    'trips' => 0,
                ];
            }
            
            $groupedEarnings[$key]['earnings'] += $trip->driver_earnings;
            $groupedEarnings[$key]['trips']++;
        }

        $groupedEarnings = array_values($groupedEarnings);

        return response()->json([
            'success' => true,
            'message' => 'Earnings breakdown retrieved',
            'data' => [
                'summary' => [
                    'total_earnings' => round($totalEarnings, 2),
                    'total_rides' => $totalRides,
                    'average_per_ride' => round($averageEarningsPerRide, 2),
                    'period' => $period,
                    'date_range' => [
                        'start' => $startDate,
                        'end' => $endDate,
                    ],
                ],
                'breakdown' => $groupedEarnings,
            ]
        ]);
    }
}