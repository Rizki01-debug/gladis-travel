<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            // 🔥 STATUS (DEFAULT)
            $table->string('trip_status')->default('ongoing');

            // 🔥 WAKTU
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();

            $table->timestamps();

            // 🔥 INDEX (BIAR CEPAT)
            $table->index('trip_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
