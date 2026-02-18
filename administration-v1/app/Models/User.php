<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'avatar',
        
        // Driver fields
        'driver_license_number',
        'driver_license_expiry',
        'vehicle_type',
        'vehicle_model',
        'vehicle_year',
        'vehicle_plate_number',
        'vehicle_color',
        'current_latitude',
        'current_longitude',
        'driver_status',
        'rating',
        'total_trips',
        'earnings',
        'is_online',
        'last_online_at',
        
        // Rider fields
        'rider_status',
        'rider_trips_count',
        'rider_rating',
        'preferred_payment_method',
        
        // Common fields
        'date_of_birth',
        'gender',
        'address',
        'city',
        'country',
        'postal_code',
        'emergency_contact_name',
        'emergency_contact_phone',
        'preferences',
        'notes',
        
        // Admin fields
        'admin_role',
        'admin_permissions',
        'department',
        
        // Status fields
        'is_verified',
        'is_active',
        'is_suspended',
        'suspended_until',
        'suspension_reason',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'driver_license_expiry' => 'date',
        'date_of_birth' => 'date',
        'suspended_until' => 'datetime',
        'last_online_at' => 'datetime',
        'last_login_at' => 'datetime',
        'preferences' => 'array',
        'admin_permissions' => 'array',
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'rider_rating' => 'decimal:2',
        'earnings' => 'decimal:2',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_suspended' => 'boolean',
        'is_online' => 'boolean',
    ];

    /**
     * Scope a query to only include drivers.
     */
    public function scopeDrivers($query)
    {
        return $query->where('type', 'driver');
    }

    /**
     * Scope a query to only include riders.
     */
    public function scopeRiders($query)
    {
        return $query->where('type', 'rider');
    }

    /**
     * Scope a query to only include admins.
     */
    public function scopeAdmins($query)
    {
        return $query->where('type', 'admin');
    }

    /**
     * Scope a query to only include online drivers.
     */
    public function scopeOnlineDrivers($query)
    {
        return $query->where('type', 'driver')
                    ->where('is_online', true)
                    ->where('is_active', true);
    }

    /**
     * Scope a query to only include available drivers.
     */
    public function scopeAvailableDrivers($query)
    {
        return $query->where('type', 'driver')
                    ->where('driver_status', 'available')
                    ->where('is_online', true)
                    ->where('is_active', true);
    }

    /**
     * Get the full name of the user.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin()
    {
        return $this->type === 'admin';
    }

    /**
     * Check if user is a driver.
     */
    public function isDriver()
    {
        return $this->type === 'driver';
    }

    /**
     * Check if user is a rider.
     */
    public function isRider()
    {
        return $this->type === 'rider';
    }

    /**
     * Check if driver is available.
     */
    public function isAvailable()
    {
        return $this->isDriver() && 
               $this->driver_status === 'available' && 
               $this->is_online && 
               $this->is_active;
    }

    /**
     * Get the driver documents.
     */
    public function driverDocuments()
    {
        return $this->hasMany(DriverDocument::class, 'driver_id');
    }

    /**
     * Get the driver location history.
     */
    public function driverLocations()
    {
        return $this->hasMany(DriverLocation::class, 'driver_id');
    }

    /**
     * Get the rider payment methods.
     */
    public function paymentMethods()
    {
        return $this->hasMany(RiderPaymentMethod::class, 'rider_id');
    }

    /**
     * Get trips as a driver.
     */
    public function driverTrips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    /**
     * Get trips as a rider.
     */
    public function riderTrips()
    {
        return $this->hasMany(Trip::class, 'rider_id');
    }

    /**
     * Update driver location.
     */
    public function updateLocation($latitude, $longitude, $speed = null, $heading = null)
    {
        if (!$this->isDriver()) {
            return false;
        }

        $this->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
        ]);

        // Record location history
        $this->driverLocations()->create([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'heading' => $heading,
            'recorded_at' => now(),
        ]);

        return true;
    }

    /**
     * Calculate distance from a point in kilometers.
     */
    public function distanceFrom($latitude, $longitude)
    {
        if (!$this->current_latitude || !$this->current_longitude) {
            return null;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($this->current_latitude);
        $lonFrom = deg2rad($this->current_longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        return $earthRadius;
    }

    /**
     * Get verified driver documents.
     */
    public function getVerifiedDocumentsAttribute()
    {
        return $this->driverDocuments()->where('is_verified', true)->get();
    }

    /**
     * Check if driver has all required documents verified.
     */
    public function getHasAllDocumentsVerifiedAttribute()
    {
        $requiredDocs = ['license_front', 'license_back', 'vehicle_registration', 'insurance'];
        
        $verifiedDocs = $this->driverDocuments()
            ->whereIn('document_type', $requiredDocs)
            ->where('is_verified', true)
            ->pluck('document_type')
            ->toArray();

        return count(array_intersect($requiredDocs, $verifiedDocs)) === count($requiredDocs);
    }
}