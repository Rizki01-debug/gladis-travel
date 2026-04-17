<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // ================= RELATION =================
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('schedule_id')
                ->constrained('departure_schedules')
                ->cascadeOnDelete();

            $table->foreignId('meeting_point_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // ================= DATA =================
            // 🔥 USER YANG MENENTUKAN TANGGAL
            $table->date('departure_date');

            $table->enum('pickup_type', [
                'meeting_point',
                'pickup_location'
            ]);

            $table->string('pickup_maps')->nullable();

            $table->string('phone', 15);

            // ================= CALCULATION =================
            $table->decimal('distance_km', 8, 2)->default(0);
            $table->decimal('price_estimation', 12, 2)->default(0);

            // ================= STATUS =================
            $table->enum('status', [
                'pending',
                'confirmed',
                'completed',
                'cancelled'
            ])->default('pending');

            $table->text('cancel_reason')->nullable();

            $table->timestamps();

            // ================= INDEX =================
            $table->index(['user_id']);
            $table->index(['schedule_id']);
            $table->index(['departure_date']);

            // ================= OPTIONAL PROTECTION =================
            // ❗ Aktifkan nanti kalau sistem sudah stabil
            // $table->unique(
            //     ['schedule_id', 'departure_date', 'user_id'],
            //     'unique_user_booking'
            // );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};