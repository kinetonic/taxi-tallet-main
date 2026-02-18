<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'pending_balance',
        'total_credited',
        'total_debited',
        'currency',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_credited' => 'decimal:2',
        'total_debited' => 'decimal:2',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // Check if wallet has sufficient balance
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    // Credit wallet
    public function credit(float $amount, string $description, ?array $metadata = null): WalletTransaction
    {
        $transaction = $this->transactions()->create([
            'transaction_id' => $this->generateTransactionId(),
            'type' => 'credit',
            'amount' => $amount,
            'balance_before' => $this->balance,
            'balance_after' => $this->balance + $amount,
            'status' => 'completed',
            'description' => $description,
            'metadata' => $metadata,
        ]);

        $this->increment('balance', $amount);
        $this->increment('total_credited', $amount);

        return $transaction;
    }

    // Debit wallet
    public function debit(float $amount, string $description, ?array $metadata = null): WalletTransaction
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient wallet balance');
        }

        $transaction = $this->transactions()->create([
            'transaction_id' => $this->generateTransactionId(),
            'type' => 'debit',
            'amount' => $amount,
            'balance_before' => $this->balance,
            'balance_after' => $this->balance - $amount,
            'status' => 'completed',
            'description' => $description,
            'metadata' => $metadata,
        ]);

        $this->decrement('balance', $amount);
        $this->increment('total_debited', $amount);

        return $transaction;
    }

    // Hold amount (for trip reservations)
    public function hold(float $amount, string $description, ?array $metadata = null): WalletTransaction
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient wallet balance');
        }

        $transaction = $this->transactions()->create([
            'transaction_id' => $this->generateTransactionId(),
            'type' => 'hold',
            'amount' => $amount,
            'balance_before' => $this->balance,
            'balance_after' => $this->balance - $amount,
            'status' => 'completed',
            'description' => $description,
            'metadata' => $metadata,
        ]);

        $this->decrement('balance', $amount);
        $this->increment('pending_balance', $amount);

        return $transaction;
    }

    // Release held amount
    public function release(float $amount, string $description, ?array $metadata = null): WalletTransaction
    {
        if ($this->pending_balance < $amount) {
            throw new \Exception('Insufficient pending balance');
        }

        $transaction = $this->transactions()->create([
            'transaction_id' => $this->generateTransactionId(),
            'type' => 'release',
            'amount' => $amount,
            'balance_before' => $this->balance,
            'balance_after' => $this->balance + $amount,
            'status' => 'completed',
            'description' => $description,
            'metadata' => $metadata,
        ]);

        $this->increment('balance', $amount);
        $this->decrement('pending_balance', $amount);

        return $transaction;
    }

    // Generate unique transaction ID
    private function generateTransactionId(): string
    {
        return 'WLT' . now()->format('YmdHis') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}