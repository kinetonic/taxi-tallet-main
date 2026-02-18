<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to use as riders and drivers
        $riders = User::where('type', 'rider')->limit(5)->get();
        $drivers = User::where('type', 'driver')->limit(5)->get();

        if ($riders->isEmpty() || $drivers->isEmpty()) {
            $this->command->warn('Please create riders and drivers first!');
            return;
        }

        $trips = [
            [
                'trip_id' => Trip::generateTripId(),
                'rider_id' => $riders[0]->id,
                'driver_id' => $drivers[0]->id,
                'pickup_address' => '123 Main St, New York, NY 10001',
                'dropoff_latitude' => 40.758896,
                'dropoff_longitude' => -73.985130,
                'dropoff_address' => '456 Broadway, New York, NY 10003',
                'vehicle_type' => 'economy',
                'distance' => 8.5,
                'duration' => 25,
                'estimated_price' => 25.50,
                'final_price' => 26.75,
                'driver_earnings' => 21.40,
                'status' => 'completed',
                'requested_at' => now()->subHours(3),
                'searching_at' => now()->subHours(3)->addSeconds(30),
                'driver_assigned_at' => now()->subHours(3)->addMinutes(1),
                'driver_arrived_at' => now()->subHours(3)->addMinutes(5),
                'started_at' => now()->subHours(3)->addMinutes(6),
                'completed_at' => now()->subHours(2)->addMinutes(30),
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'paid_at' => now()->subHours(2)->addMinutes(31),
                'rider_rating' => 5,
                'rider_review' => 'Great driver, smooth ride!',
                'driver_rating' => 5,
                'driver_review' => 'Nice passenger',
                'waiting_time' => 4,
                'surge_multiplier' => 1.0,
                'additional_charges' => [
                    ['name' => 'Toll fee', 'amount' => 3.50],
                    ['name' => 'Service fee', 'amount' => 1.75]
                ],
                'assigned_drivers_history' => [$drivers[0]->id],
                'driver_search_count' => 1,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(2)->addMinutes(30),
            ],
            [
                'trip_id' => Trip::generateTripId(),
                'rider_id' => $riders[1]->id,
                'driver_id' => $drivers[0]->id,
                'pickup_address' => '789 Hollywood Blvd, Los Angeles, CA 90028',
                'dropoff_latitude' => 34.073620,
                'dropoff_longitude' => -118.400356,
                'dropoff_address' => '101 Santa Monica Blvd, Santa Monica, CA 90401',
                'vehicle_type' => 'premium',
                'distance' => 15.2,
                'duration' => 45,
                'estimated_price' => 48.75,
                'final_price' => 50.25,
                'driver_earnings' => 40.20,
                'status' => 'started',
                'requested_at' => now()->subHours(1),
                'searching_at' => now()->subHours(1)->addSeconds(45),
                'driver_assigned_at' => now()->subHours(1)->addMinutes(2),
                'driver_arrived_at' => now()->subHours(1)->addMinutes(8),
                'started_at' => now()->subHours(1)->addMinutes(10),
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'surge_multiplier' => 1.2,
                'additional_charges' => [
                    ['name' => 'Premium vehicle fee', 'amount' => 5.00]
                ],
                'assigned_drivers_history' => [$drivers[0]->id],
                'driver_search_count' => 1,
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1)->addMinutes(10),
            ]
        ];

        foreach ($trips as $trip) {
            Trip::create($trip);
        }

        $this->command->info('5 test trips created successfully!');
    }
}