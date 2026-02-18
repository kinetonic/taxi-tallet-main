<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MapController extends Controller
{
    // Get nearby available drivers
    public function getNearbyDrivers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.5|max:20', // in kilometers
            'vehicle_type' => 'nullable|in:economy,comfort,premium,van,bike',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 5; // default 5km
        $vehicleType = $request->vehicle_type;

        $drivers = User::where('type', 'driver')
            ->where('is_online', true)
            ->where('driver_status', 'available')
            ->where('is_active', true)
            ->where('is_verified', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->where(function($query) use ($vehicleType) {
                if ($vehicleType) {
                    $query->where('vehicle_type', $vehicleType)
                          ->orWhereNull('vehicle_type');
                }
            })
            ->selectRaw("
                id, first_name, last_name, 
                current_latitude, current_longitude,
                vehicle_type, vehicle_model, vehicle_color,
                driver_status, rating, total_trips,
                (6371 * acos(cos(radians(?)) * cos(radians(current_latitude)) * 
                cos(radians(current_longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(current_latitude)))) as distance
            ", [$latitude, $longitude, $latitude])
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->limit(50)
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'driver_name' => $driver->full_name,
                    'latitude' => $driver->current_latitude,
                    'longitude' => $driver->current_longitude,
                    'vehicle_type' => $driver->vehicle_type,
                    'vehicle_model' => $driver->vehicle_model,
                    'vehicle_color' => $driver->vehicle_color,
                    'rating' => $driver->rating,
                    'total_trips' => $driver->total_trips,
                    'distance' => round($driver->distance, 2),
                    'eta' => round(($driver->distance / 30) * 60), // 30 km/h average
                    'available' => $driver->driver_status === 'available',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Nearby drivers retrieved',
            'data' => [
                'drivers' => $drivers,
                'count' => $drivers->count(),
                'center' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ],
                'radius' => $radius,
            ]
        ]);
    }

    // Update driver location
    public function updateDriverLocation(Request $request)
    {
        $driver = $request->user();

        if (!$driver->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'Only drivers can update location'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update user current location
            $driver->update([
                'current_latitude' => $request->latitude,
                'current_longitude' => $request->longitude,
            ]);

            // Save to location history
            $driver->driverLocations()->create([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'speed' => $request->speed,
                'heading' => $request->heading,
                'accuracy' => $request->accuracy,
                'recorded_at' => now(),
            ]);

            // If driver is on a trip, update trip with location
            $activeTrip = Trip::where('driver_id', $driver->id)
                ->whereIn('status', ['driver_assigned', 'driver_arrived', 'started'])
                ->first();

            if ($activeTrip) {
                // Broadcast location update to rider (via WebSocket)
                // $this->broadcastDriverLocation($activeTrip->rider_id, $driver);
            }

            return response()->json([
                'success' => true,
                'message' => 'Location updated',
                'data' => [
                    'latitude' => $driver->current_latitude,
                    'longitude' => $driver->current_longitude,
                    'updated_at' => now(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get driver location (for rider tracking)
    public function getDriverLocation(Trip $trip)
    {
        $user = request()->user();

        if ($user->id !== $trip->rider_id && $user->id !== $trip->driver_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view driver location'
            ], 403);
        }

        if (!$trip->driver_id) {
            return response()->json([
                'success' => false,
                'message' => 'No driver assigned to this trip'
            ], 404);
        }

        $driver = User::find($trip->driver_id);

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Driver location retrieved',
            'data' => [
                'driver' => [
                    'id' => $driver->id,
                    'name' => $driver->full_name,
                    'vehicle_model' => $driver->vehicle_model,
                    'vehicle_color' => $driver->vehicle_color,
                    'rating' => $driver->rating,
                ],
                'location' => [
                    'latitude' => $driver->current_latitude,
                    'longitude' => $driver->current_longitude,
                    'updated_at' => $driver->updated_at,
                ],
                'trip' => [
                    'status' => $trip->status,
                    'estimated_arrival' => $trip->getEstimatedArrivalTime(),
                ]
            ]
        ]);
    }

    // Calculate fare estimate
    public function calculateFareEstimate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_latitude' => 'required|numeric|between:-90,90',
            'pickup_longitude' => 'required|numeric|between:-180,180',
            'dropoff_latitude' => 'required|numeric|between:-90,90',
            'dropoff_longitude' => 'required|numeric|between:-180,180',
            'vehicle_type' => 'required|in:economy,comfort,premium,van,bike',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Calculate distance
        $distance = $this->haversineDistance(
            $request->pickup_latitude,
            $request->pickup_longitude,
            $request->dropoff_latitude,
            $request->dropoff_longitude
        );

        // Calculate duration (assuming 30 km/h average speed)
        $duration = round(($distance / 30) * 60);

        // Calculate price
        $baseFare = 3.00; // TND
        $perKmRate = 1.50; // TND
        
        $multipliers = [
            'economy' => 1.0,
            'comfort' => 1.3,
            'premium' => 1.7,
            'van' => 2.0,
            'bike' => 0.7,
        ];

        $multiplier = $multipliers[$request->vehicle_type] ?? 1.0;
        $price = round(($baseFare + ($distance * $perKmRate)) * $multiplier, 2);

        // Check for surge pricing during peak hours
        $surgeMultiplier = $this->calculateSurgeMultiplier($request->pickup_latitude, $request->pickup_longitude);
        $finalPrice = round($price * $surgeMultiplier, 2);

        return response()->json([
            'success' => true,
            'message' => 'Fare estimate calculated',
            'data' => [
                'distance' => round($distance, 2) . ' km',
                'distance_km' => round($distance, 2),
                'duration' => $duration . ' min',
                'duration_minutes' => $duration,
                'base_fare' => $baseFare,
                'per_km_rate' => $perKmRate,
                'vehicle_type_multiplier' => $multiplier,
                'surge_multiplier' => $surgeMultiplier,
                'estimated_price' => $finalPrice,
                'currency' => 'TND',
                'breakdown' => [
                    'base_fare' => $baseFare,
                    'distance_fare' => round($distance * $perKmRate, 2),
                    'vehicle_type_surcharge' => round(($baseFare + ($distance * $perKmRate)) * ($multiplier - 1), 2),
                    'surge_price' => round(($price * $surgeMultiplier) - $price, 2),
                ]
            ]
        ]);
    }

    // Get address from coordinates (reverse geocoding)
    public function reverseGeocode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Using Nominatim (OpenStreetMap) for free geocoding
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$request->latitude}&lon={$request->longitude}&zoom=18&addressdetails=1";
            
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url, [
                'headers' => [
                    'User-Agent' => 'TaxiApp/1.0 (contact@yourapp.com)',
                    'Accept' => 'application/json',
                    'Accept-Language' => 'fr',
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['address'])) {
                $address = $data['address'];
                $formattedAddress = $this->formatAddress($address);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Address retrieved',
                    'data' => [
                        'address' => $formattedAddress,
                        'components' => $address,
                        'full_display_name' => $data['display_name'] ?? null,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Search addresses (autocomplete)
    public function searchAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:3',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = urlencode($request->query);
            $limit = $request->limit ?? 5;
            
            $url = "https://nominatim.openstreetmap.org/search?format=json&q={$query}&limit={$limit}&addressdetails=1";
            
            // If coordinates provided, add viewbox for bias
            if ($request->latitude && $request->longitude) {
                $lat = $request->latitude;
                $lng = $request->longitude;
                $url .= "&viewbox=" . ($lng - 0.1) . "," . ($lat + 0.1) . "," . ($lng + 0.1) . "," . ($lat - 0.1);
            }

            $client = new \GuzzleHttp\Client();
            $response = $client->get($url, [
                'headers' => [
                    'User-Agent' => 'TaxiApp/1.0 (contact@yourapp.com)',
                    'Accept' => 'application/json',
                    'Accept-Language' => 'fr',
                ]
            ]);

            $results = json_decode($response->getBody(), true);

            $formattedResults = array_map(function ($result) {
                return [
                    'display_name' => $result['display_name'],
                    'latitude' => $result['lat'],
                    'longitude' => $result['lon'],
                    'address' => $result['address'] ?? null,
                    'type' => $result['type'] ?? null,
                    'importance' => $result['importance'] ?? null,
                ];
            }, $results);

            return response()->json([
                'success' => true,
                'message' => 'Address search results',
                'data' => $formattedResults
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search addresses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper methods
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    private function calculateSurgeMultiplier($latitude, $longitude)
    {
        // Check if it's peak hours (7-9 AM, 5-7 PM on weekdays)
        $hour = now()->hour;
        $isWeekday = now()->isWeekday();
        $isPeakHour = ($isWeekday && (($hour >= 7 && $hour <= 9) || ($hour >= 17 && $hour <= 19)));
        
        // Check driver availability in area
        $availableDrivers = User::where('type', 'driver')
            ->where('is_online', true)
            ->where('driver_status', 'available')
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->whereRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(current_latitude)) * 
                cos(radians(current_longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(current_latitude)))) < 3
            ", [$latitude, $longitude, $latitude])
            ->count();

        // Calculate surge multiplier based on demand and supply
        $baseMultiplier = 1.0;
        
        if ($isPeakHour) {
            $baseMultiplier += 0.3;
        }
        
        if ($availableDrivers < 5) {
            $baseMultiplier += 0.5;
        } elseif ($availableDrivers < 10) {
            $baseMultiplier += 0.2;
        }

        return min($baseMultiplier, 2.5); // Cap at 2.5x
    }

    private function formatAddress($addressComponents)
    {
        $parts = [];
        
        if (!empty($addressComponents['house_number'])) {
            $parts[] = $addressComponents['house_number'];
        }
        
        if (!empty($addressComponents['road'])) {
            $parts[] = $addressComponents['road'];
        }
        
        if (!empty($addressComponents['suburb'])) {
            $parts[] = $addressComponents['suburb'];
        }
        
        if (!empty($addressComponents['city'])) {
            $parts[] = $addressComponents['city'];
        }
        
        if (!empty($addressComponents['state'])) {
            $parts[] = $addressComponents['state'];
        }
        
        if (!empty($addressComponents['country'])) {
            $parts[] = $addressComponents['country'];
        }
        
        return implode(', ', array_filter($parts));
    }
}