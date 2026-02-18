<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiderPaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rider_id',
        'type',
        'provider',
        'payment_method_id',
        'card_last_four',
        'card_brand',
        'expiry_month',
        'expiry_year',
        'mobile_network',
        'mobile_number',
        'wallet_balance',
        'is_default',
        'is_verified',
        'metadata',
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
        'metadata' => 'array',
    ];

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    // Get masked card number
    public function getMaskedCardNumber(): ?string
    {
        if (!$this->card_last_four) {
            return null;
        }

        return '**** **** **** ' . $this->card_last_four;
    }

    // Check if card is expired
    public function isCardExpired(): bool
    {
        if (!$this->expiry_month || !$this->expiry_year) {
            return false;
        }

        $expiryDate = \Carbon\Carbon::createFromDate($this->expiry_year, $this->expiry_month, 1)->endOfMonth();
        
        return now()->greaterThan($expiryDate);
    }
}