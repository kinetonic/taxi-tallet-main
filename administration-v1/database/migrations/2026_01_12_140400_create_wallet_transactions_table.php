<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->string('transaction_id')->unique();
            
            $table->enum('type', ['credit', 'debit', 'hold', 'release']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('description');
            $table->string('reference')->nullable()->comment('Payment reference, trip ID, etc.');
            
            // Related models
            $table->foreignId('trip_id')->nullable()->constrained('trips')->onDelete('set null');
            $table->foreignId('payment_method_id')->nullable()->constrained('rider_payment_methods')->onDelete('set null');
            
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['wallet_id', 'created_at']);
            $table->index('transaction_id');
            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};