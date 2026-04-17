<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_earnings', function (Blueprint $table) {
            $table->id();

            // ================= RELATION =================
            $table->foreignId('driver_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('booking_id')
                ->constrained()
                ->cascadeOnDelete();

            // ================= DATA =================
            $table->decimal('amount', 12, 2);

            // ================= STATUS =================
            $table->enum('status', ['unpaid', 'paid'])
                ->default('unpaid');

            $table->timestamps();

            // ================= PROTECTION =================
            // ❗ 1 booking hanya boleh 1 earning
            $table->unique('booking_id');

            // ================= PERFORMANCE =================
            $table->index('driver_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_earnings');
    }
};
