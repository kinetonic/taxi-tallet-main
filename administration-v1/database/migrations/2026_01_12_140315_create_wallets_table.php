<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->unique()->onDelete('cascade');
            
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('pending_balance', 10, 2)->default(0);
            $table->decimal('total_credited', 10, 2)->default(0);
            $table->decimal('total_debited', 10, 2)->default(0);
            
            $table->string('currency', 3)->default('TND');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};