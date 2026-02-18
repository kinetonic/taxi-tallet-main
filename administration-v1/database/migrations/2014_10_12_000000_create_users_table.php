<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['admin', 'driver', 'rider'])->default('rider');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            
            // Driver specific fields
            $table->string('driver_license_number')->nullable();
            $table->date('driver_license_expiry')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_year')->nullable();
            $table->string('vehicle_plate_number')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->enum('driver_status', ['available', 'on_trip', 'offline', 'maintenance'])->default('offline');
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_trips')->default(0);
            $table->decimal('earnings', 10, 2)->default(0.00);
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_online_at')->nullable();
            
            // Rider specific fields
            $table->enum('rider_status', ['active', 'inactive', 'suspended'])->default('active');
            $table->integer('rider_trips_count')->default(0);
            $table->decimal('rider_rating', 3, 2)->default(0.00);
            $table->string('preferred_payment_method')->nullable();
            
            // Common fields
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Tunisia');
            $table->string('postal_code')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->json('preferences')->nullable();
            $table->text('notes')->nullable();
            
            // Admin specific fields
            $table->enum('admin_role', ['super_admin', 'manager', 'support', 'analyst'])->nullable();
            $table->json('admin_permissions')->nullable();
            $table->string('department')->nullable();
            
            // Status and verification
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_suspended')->default(false);
            $table->timestamp('suspended_until')->nullable();
            $table->text('suspension_reason')->nullable();
            
            // Timestamps
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['type', 'is_active']);
            $table->index(['driver_status', 'is_online']);
            $table->index(['type', 'driver_status']);
            $table->index('last_online_at');
        });

        // Create driver documents table
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users');
            $table->enum('document_type', [
                'license_front',
                'license_back', 
                'vehicle_registration',
                'insurance',
                'profile_photo',
                'vehicle_photo',
                'other'
            ]);
            $table->string('document_path');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index(['driver_id', 'is_verified']);
        });

        // Create driver locations history table
        Schema::create('driver_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('heading', 5, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index(['driver_id', 'recorded_at']);
            $table->index('recorded_at');
        });

        // Create rider payment methods table
        Schema::create('rider_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('users');
            $table->enum('type', ['credit_card', 'debit_card', 'paypal', 'cash', 'mobile_money']);
            $table->string('provider')->nullable();
            $table->string('account_number')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['rider_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rider_payment_methods');
        Schema::dropIfExists('driver_locations');
        Schema::dropIfExists('driver_documents');
        Schema::dropIfExists('users');
    }
};