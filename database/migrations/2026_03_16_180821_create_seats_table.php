<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();

            // ================= RELATION =================
            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnDelete();

            // ================= DATA =================
            $table->unsignedInteger('seat_number');

            // 🔥 FLEXIBLE (lebih baik dari hardcode)
            $table->boolean('is_driver_seat')->default(false);

            $table->timestamps();

            // ================= PROTECTION =================
            // ❗ tidak boleh ada nomor kursi sama dalam 1 kendaraan
            $table->unique(['vehicle_id', 'seat_number']);

            // ================= PERFORMANCE =================
            $table->index('vehicle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
