<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'document_type',
        'document_number',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'is_verified',
        'verified_by',
        'verified_at',
        'verification_notes',
        'expiry_date',
        'is_expired',
        'is_rejected',
        'rejection_reason',
        'rejected_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_expired' => 'boolean',
        'is_rejected' => 'boolean',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Check if document is expiring soon (within 30 days)
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return now()->diffInDays($this->expiry_date, false) <= 30;
    }
}