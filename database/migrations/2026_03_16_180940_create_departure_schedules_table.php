<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departure_schedules', function (Blueprint $table) {
            $table->id();

            // ================= RELATION =================
            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('origin_city_id')
                ->constrained('cities')
                ->cascadeOnDelete();

            $table->foreignId('destination_city_id')
                ->constrained('cities')
                ->cascadeOnDelete();

            // ================= TIME =================
            // 🔥 HANYA JAM (SESUAI ARSITEKTUR FLEXIBLE)
            $table->time('departure_time');

            // ================= STATUS =================
            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->timestamps();

            // ================= INDEX =================
            $table->index(['vehicle_id']);
            $table->index(['origin_city_id']);
            $table->index(['destination_city_id']);

            // ================= OPTIONAL UNIQUE =================
            // Aktifkan kalau sistem sudah stabil
            // $table->unique(
            //     ['vehicle_id', 'origin_city_id', 'destination_city_id', 'departure_time'],
            //     'unique_schedule'
            // );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departure_schedules');
    }
};