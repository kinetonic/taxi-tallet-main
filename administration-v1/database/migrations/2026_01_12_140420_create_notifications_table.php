<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'success', 'warning', 'error', 'trip_update', 'payment', 'system']);
            $table->json('data')->nullable();
            
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_read']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};