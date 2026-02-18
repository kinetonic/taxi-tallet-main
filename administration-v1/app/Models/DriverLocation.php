<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'accuracy',
        'altitude',
        'altitude_accuracy',
        'additional_data',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'accuracy' => 'decimal:2',
        'altitude' => 'decimal:2',
        'altitude_accuracy' => 'decimal:2',
        'additional_data' => 'array',
        'recorded_at' => 'datetime',
    ];

    public $timestamps = false;

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}