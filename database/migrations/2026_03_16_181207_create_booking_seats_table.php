<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_seats', function (Blueprint $table) {
            $table->id();

            // ================= RELATION =================
            $table->foreignId('booking_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('seat_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            // ================= PROTECTION =================
            // ❗ 1 kursi tidak boleh dipakai 2 booking aktif
            $table->unique(['booking_id', 'seat_id']);

            // ================= PERFORMANCE =================
            $table->index('seat_id');
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_seats');
    }
};
