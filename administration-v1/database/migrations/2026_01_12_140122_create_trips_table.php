<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_id')->unique(); // Custom trip ID: TRIP-2024-0001
            
            // Relationships
            $table->foreignId('rider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Location data
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 10, 8);
            $table->string('pickup_address');
            $table->decimal('dropoff_latitude', 10, 8);
            $table->decimal('dropoff_longitude', 10, 8);
            $table->string('dropoff_address');
            
            // Trip details
            $table->enum('vehicle_type', ['economy', 'comfort', 'premium', 'van', 'bike'])->default('economy');
            $table->decimal('distance', 8, 2)->comment('in kilometers');
            $table->integer('duration')->comment('in minutes');
            $table->decimal('estimated_price', 10, 2);
            $table->decimal('final_price', 10, 2)->nullable();
            $table->decimal('driver_earnings', 10, 2)->nullable();
            
            // Status tracking
            $table->enum('status', [
                'pending',           // Rider requested trip
                'searching',         // Looking for drivers
                'driver_assigned',   // Driver accepted
                'driver_arrived',    // Driver at pickup
                'started',          // Trip started
                'completed',        // Trip completed
                'cancelled',        // Trip cancelled
                'no_drivers',       // No drivers available
                'rider_cancelled',  // Rider cancelled
                'driver_cancelled'  // Driver cancelled
            ])->default('pending');
            
            // Timestamps for each status
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('searching_at')->nullable();
            $table->timestamp('driver_assigned_at')->nullable();
            $table->timestamp('driver_arrived_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Payment
            $table->enum('payment_method', ['cash', 'card', 'mobile_money', 'wallet'])->default('cash');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Ratings
            $table->integer('rider_rating')->nullable()->comment('1-5');
            $table->text('rider_review')->nullable();
            $table->integer('driver_rating')->nullable()->comment('1-5');
            $table->text('driver_review')->nullable();
            
            // Additional info
            $table->json('route_polyline')->nullable();
            $table->decimal('waiting_time', 5, 2)->nullable()->comment('in minutes');
            $table->decimal('surge_multiplier', 3, 2)->default(1.00);
            $table->json('additional_charges')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->enum('cancelled_by', ['rider', 'driver', 'system'])->nullable();
            
            // Track changes
            $table->integer('driver_search_count')->default(0);
            $table->json('assigned_drivers_history')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['rider_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index('status');
            $table->index('created_at');
            $table->index(['pickup_latitude', 'pickup_longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};