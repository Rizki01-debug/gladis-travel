<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('departure_schedules');
            $table->date('departure_date');
            $table->string('pickup_type');
            $table->foreignId('meeting_point_id')->nullable()->constrained();
            $table->string('pickup_maps')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('price_estimation', 10, 2)->nullable();
            $table->decimal('pickup_fee', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('cancel_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
