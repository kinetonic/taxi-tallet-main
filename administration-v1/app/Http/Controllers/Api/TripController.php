<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TripController extends Controller
{
    // Request a new trip
    public function requestTrip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_latitude' => 'required|numeric|between:-90,90',
            'pickup_longitude' => 'required|numeric|between:-180,180',
            'pickup_address' => 'required|string|max:255',
            'dropoff_latitude' => 'required|numeric|between:-90,90',
            'dropoff_longitude' => 'required|numeric|between:-180,180',
            'dropoff_address' => 'required|string|max:255',
            'vehicle_type' => 'required|in:economy,comfort,premium,van,bike',
            'payment_method' => 'required|in:cash,card,mobile_money,wallet',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            
            // Calculate distance and price
            $distance = $this->calculateDistance(
                $request->pickup_latitude,
                $request->pickup_longitude,
                $request->dropoff_latitude,
                $request->dropoff_longitude
            );
            
            $duration = $this->calculateEstimatedDuration($distance);
            $estimatedPrice = $this->calculatePrice($distance, $request->vehicle_type);

            // Create trip
            $trip = Trip::create([
                'trip_id' => Trip::generateTripId(),
                'rider_id' => $user->id,
                'pickup_latitude' => $request->pickup_latitude,
                'pickup_longitude' => $request->pickup_longitude,
                'pickup_address' => $request->pickup_address,
                'dropoff_latitude' => $request->dropoff_latitude,
                'dropoff_longitude' => $request->dropoff_longitude,
                'dropoff_address' => $request->dropoff_address,
                'vehicle_type' => $request->vehicle_type,
                'distance' => $distance,
                'duration' => $duration,
                'estimated_price' => $estimatedPrice,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            // If payment method is wallet, hold the amount
            if ($request->payment_method === 'wallet') {
                $wallet = $user->wallet;
                if (!$wallet || !$wallet->hasSufficientBalance($estimatedPrice)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance'
                    ], 400);
                }
                
                $wallet->hold($estimatedPrice, "Hold for trip: {$trip->trip_id}", [
                    'trip_id' => $trip->id,
                    'estimated_price' => $estimatedPrice,
                ]);
            }

            // Find nearby drivers
            $nearbyDrivers = $this->findNearbyDrivers(
                $request->pickup_latitude,
                $request->pickup_longitude,
                $request->vehicle_type
            );

            // Update trip with driver search
            $trip->update([
                'status' => 'searching',
                'searching_at' => now(),
                'driver_search_count' => count($nearbyDrivers),
                'assigned_drivers_history' => $nearbyDrivers->pluck('id')->toArray(),
            ]);

            DB::commit();

            // Send notifications to nearby drivers (implement with WebSocket/Pusher)
            $this->notifyNearbyDrivers($nearbyDrivers, $trip);

            return response()->json([
                'success' => true,
                'message' => 'Trip requested successfully',
                'data' => [
                    'trip' => $trip,
                    'nearby_drivers_count' => count($nearbyDrivers),
                    'estimated_arrival' => $trip->getEstimatedArrivalTime(),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to request trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get current trip for user
    public function getCurrentTrip(Request $request)
    {
        $user = $request->user();
        
        $trip = Trip::where(function($query) use ($user) {
                $query->where('rider_id', $user->id)
                      ->orWhere('driver_id', $user->id);
            })
            ->whereIn('status', ['pending', 'searching', 'driver_assigned', 'driver_arrived', 'started'])
            ->latest()
            ->first();

        if (!$trip) {
            return response()->json([
                'success' => false,
                'message' => 'No active trip found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Current trip retrieved',
            'data' => $trip
        ]);
    }

    // Driver accepts trip
    public function acceptTrip(Request $request, Trip $trip)
    {
        $driver = $request->user();

        if (!$driver->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'Only drivers can accept trips'
            ], 403);
        }

        if ($trip->status !== 'searching') {
            return response()->json([
                'success' => false,
                'message' => 'Trip is not available for acceptance'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Update trip
            $trip->update([
                'driver_id' => $driver->id,
                'status' => 'driver_assigned',
                'driver_assigned_at' => now(),
            ]);

            // Update driver status
            $driver->update([
                'driver_status' => 'on_ride',
                'is_online' => false,
            ]);

            // Send notification to rider
            $this->sendNotification($trip->rider_id, 'Driver Assigned', "{$driver->full_name} has accepted your ride request", 'trip_update', [
                'trip_id' => $trip->id,
                'driver' => $driver,
                'estimated_arrival' => $trip->getEstimatedArrivalTime(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Trip accepted successfully',
                'data' => $trip
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Start trip
    public function startTrip(Request $request, Trip $trip)
    {
        $driver = $request->user();

        if ($trip->driver_id !== $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to start this trip'
            ], 403);
        }

        if ($trip->status !== 'driver_arrived') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot start trip. Driver must arrive first.'
            ], 400);
        }

        $trip->update([
            'status' => 'started',
            'started_at' => now(),
        ]);

        // Send notification to rider
        $this->sendNotification($trip->rider_id, 'Trip Started', 'Your ride has started', 'trip_update');

        return response()->json([
            'success' => true,
            'message' => 'Trip started',
            'data' => $trip
        ]);
    }

    // Complete trip
    public function completeTrip(Request $request, Trip $trip)
    {
        $driver = $request->user();

        if ($trip->driver_id !== $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to complete this trip'
            ], 403);
        }

        if ($trip->status !== 'started') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot complete trip that has not started'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Calculate final price
            $finalPrice = $this->calculateFinalPrice($trip);
            $driverEarnings = $finalPrice * 0.8; // 80% to driver

            // Update trip
            $trip->update([
                'status' => 'completed',
                'completed_at' => now(),
                'final_price' => $finalPrice,
                'driver_earnings' => $driverEarnings,
            ]);

            // Update driver
            $driver->increment('total_trips');
            $driver->increment('earnings', $driverEarnings);
            $driver->update([
                'driver_status' => 'available',
                'is_online' => true,
            ]);

            // Process payment
            $this->processTripPayment($trip);

            // Send notifications
            $this->sendNotification($trip->rider_id, 'Trip Completed', 'Your ride has been completed', 'trip_update');
            $this->sendNotification($driver->id, 'Trip Completed', "Trip completed. Earnings: {$driverEarnings} TND", 'payment');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Trip completed successfully',
                'data' => $trip
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Cancel trip
    public function cancelTrip(Request $request, Trip $trip)
    {
        $user = $request->user();

        if (!$trip->canBeCancelledBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this trip'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $cancelledBy = $user->isDriver() ? 'driver' : 'rider';

            $trip->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->reason,
                'cancelled_by' => $cancelledBy,
            ]);

            // If driver cancels, update their status
            if ($user->isDriver()) {
                $user->update([
                    'driver_status' => 'available',
                    'is_online' => true,
                ]);
            }

            // Release held wallet amount if any
            if ($trip->payment_method === 'wallet') {
                $wallet = $trip->rider->wallet;
                if ($wallet) {
                    $wallet->release($trip->estimated_price, "Release cancelled trip: {$trip->trip_id}");
                }
            }

            // Send notification to other party
            $otherUserId = $cancelledBy === 'driver' ? $trip->rider_id : $trip->driver_id;
            if ($otherUserId) {
                $this->sendNotification($otherUserId, 'Trip Cancelled', "Trip cancelled by {$cancelledBy}", 'trip_update', [
                    'reason' => $request->reason,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Trip cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Rate trip
    public function rateTrip(Request $request, Trip $trip)
    {
        $user = $request->user();
        $isRider = $user->id === $trip->rider_id;
        $isDriver = $user->id === $trip->driver_id;

        if (!$isRider && !$isDriver) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to rate this trip'
            ], 403);
        }

        if ($trip->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Can only rate completed trips'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($isRider) {
            $trip->update([
                'driver_rating' => $request->rating,
                'driver_review' => $request->review,
            ]);

            // Update driver's average rating
            $this->updateUserRating($trip->driver_id);
        } else {
            $trip->update([
                'rider_rating' => $request->rating,
                'rider_review' => $request->review,
            ]);

            // Update rider's average rating
            $this->updateUserRating($trip->rider_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully'
        ]);
    }

    // Get trip history
    public function tripHistory(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 10);

        $query = Trip::where('rider_id', $user->id)
            ->orWhere('driver_id', $user->id)
            ->orderBy('created_at', 'desc');

        $trips = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Trip history retrieved',
            'data' => $trips
        ]);
    }

    // Helper methods
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    private function calculateEstimatedDuration($distance)
    {
        // Average speed: 30 km/h in city
        return round(($distance / 30) * 60);
    }

    private function calculatePrice($distance, $vehicleType)
    {
        $baseFare = 3.00; // Base fare in TND
        $perKmRate = 1.50; // Per km rate in TND

        // Vehicle type multipliers
        $multipliers = [
            'economy' => 1.0,
            'comfort' => 1.3,
            'premium' => 1.7,
            'van' => 2.0,
            'bike' => 0.7,
        ];

        $multiplier = $multipliers[$vehicleType] ?? 1.0;
        $price = ($baseFare + ($distance * $perKmRate)) * $multiplier;

        return round($price, 2);
    }

    private function calculateFinalPrice(Trip $trip)
    {
        // For now, return estimated price
        // In production, you might adjust based on actual distance/time
        return $trip->estimated_price;
    }

    private function findNearbyDrivers($latitude, $longitude, $vehicleType, $radius = 5)
    {
        return User::where('type', 'driver')
            ->where('is_online', true)
            ->where('driver_status', 'available')
            ->where('is_active', true)
            ->where('is_verified', true)
            ->where(function($query) use ($vehicleType) {
                if ($vehicleType) {
                    $query->where('vehicle_type', $vehicleType)
                          ->orWhereNull('vehicle_type');
                }
            })
            ->whereRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(current_latitude)) * 
                cos(radians(current_longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(current_latitude)))) < ?
            ", [$latitude, $longitude, $latitude, $radius])
            ->orderByRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(current_latitude)) * 
                cos(radians(current_longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(current_latitude))))
            ", [$latitude, $longitude, $latitude])
            ->limit(10)
            ->get();
    }

    private function updateUserRating($userId)
    {
        $user = User::find($userId);
        
        if ($user->isDriver()) {
            $avgRating = Trip::where('driver_id', $userId)
                ->whereNotNull('driver_rating')
                ->avg('driver_rating');
                
            $user->update(['rating' => round($avgRating, 2)]);
        } else {
            $avgRating = Trip::where('rider_id', $userId)
                ->whereNotNull('rider_rating')
                ->avg('rider_rating');
                
            $user->update(['rider_rating' => round($avgRating, 2)]);
        }
    }

    private function processTripPayment(Trip $trip)
    {
        if ($trip->payment_method === 'cash') {
            $trip->update(['payment_status' => 'pending']);
        } elseif ($trip->payment_method === 'wallet') {
            $wallet = $trip->rider->wallet;
            
            // Release held amount
            $wallet->release($trip->estimated_price, "Release for completed trip: {$trip->trip_id}");
            
            // Charge final price
            $wallet->debit($trip->final_price, "Payment for trip: {$trip->trip_id}", [
                'trip_id' => $trip->id,
            ]);
            
            // Credit driver
            $driverWallet = $trip->driver->wallet;
            $driverWallet->credit($trip->driver_earnings, "Earnings from trip: {$trip->trip_id}", [
                'trip_id' => $trip->id,
            ]);
            
            $trip->update(['payment_status' => 'paid', 'paid_at' => now()]);
        }
        // Add other payment methods (card, mobile money) integration
    }

    private function sendNotification($userId, $title, $message, $type = 'info', $data = null)
    {
        // Store in database
        \App\Models\Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
        ]);

        // Send push notification (implement with Firebase Cloud Messaging)
        // $this->sendPushNotification($userId, $title, $message, $data);
    }

    private function notifyNearbyDrivers($drivers, $trip)
    {
        foreach ($drivers as $driver) {
            $this->sendNotification($driver->id, 'New Ride Request', 'A new ride is available near you', 'trip_update', [
                'trip_id' => $trip->id,
                'pickup_address' => $trip->pickup_address,
                'estimated_price' => $trip->estimated_price,
                'distance' => $trip->distance,
            ]);
        }
    }
}