<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'rider_id',
        'driver_id',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_address',
        'dropoff_latitude',
        'dropoff_longitude',
        'dropoff_address',
        'vehicle_type',
        'distance',
        'duration',
        'estimated_price',
        'final_price',
        'driver_earnings',
        'status',
        'requested_at',
        'searching_at',
        'driver_assigned_at',
        'driver_arrived_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'payment_method',
        'payment_status',
        'payment_id',
        'paid_at',
        'rider_rating',
        'rider_review',
        'driver_rating',
        'driver_review',
        'route_polyline',
        'waiting_time',
        'surge_multiplier',
        'additional_charges',
        'cancellation_reason',
        'cancelled_by',
        'driver_search_count',
        'assigned_drivers_history',
    ];

    protected $casts = [
        'estimated_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'driver_earnings' => 'decimal:2',
        'distance' => 'decimal:2',
        'duration' => 'integer',
        'additional_charges' => 'array',
        'assigned_drivers_history' => 'array',
        'route_polyline' => 'array',
        'requested_at' => 'datetime',
        'searching_at' => 'datetime',
        'driver_assigned_at' => 'datetime',
        'driver_arrived_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(RiderPaymentMethod::class, 'payment_method_id');
    }

    // Generate trip ID
    public static function generateTripId(): string
    {
        $date = now()->format('Ymd');
        $latest = self::where('trip_id', 'like', "TRIP-{$date}-%")
            ->orderBy('trip_id', 'desc')
            ->first();

        $number = $latest ? (int) substr($latest->trip_id, -4) + 1 : 1;

        return sprintf('TRIP-%s-%04d', $date, $number);
    }

    // Calculate driver earnings (80% of final price)
    public function calculateDriverEarnings(): float
    {
        return $this->final_price * 0.8;
    }

    // Check if trip can be cancelled
    public function canBeCancelledBy($user): bool
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return false;
        }

        // Rider can cancel before driver arrives
        if ($user->id === $this->rider_id && !$this->driver_arrived_at) {
            return true;
        }

        // Driver can cancel before trip starts
        if ($user->id === $this->driver_id && !$this->started_at) {
            return true;
        }

        return false;
    }

    // Get estimated arrival time
    public function getEstimatedArrivalTime(): ?\Carbon\Carbon
    {
        if (!$this->driver_assigned_at) {
            return null;
        }

        return $this->driver_assigned_at->addMinutes($this->duration);
    }


public function scopeActive($query)
{
    return $query->whereIn('status', ['pending', 'searching', 'driver_assigned', 'driver_arrived', 'started']);
}

public function scopeCompleted($query)
{
    return $query->where('status', 'completed');
}

public function scopeCancelled($query)
{
    return $query->where('status', 'cancelled');
}

public function scopeRecent($query, $days = 7)
{
    return $query->where('created_at', '>=', now()->subDays($days));
}

public function getTotalEarningsAttribute()
{
    return $this->driver_earnings ?? 0;
}

}