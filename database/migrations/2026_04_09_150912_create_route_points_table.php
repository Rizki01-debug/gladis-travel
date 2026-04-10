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
        Schema::create('route_points', function (Blueprint $table) {
            $table->id();

            $table->foreignId('schedule_id')
                ->constrained('departure_schedules')
                ->onDelete('cascade');

            $table->foreignId('meeting_point_id')
                ->constrained('meeting_points')
                ->onDelete('cascade');

            $table->integer('order')->default(0); // 🔥 urutan titik

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_points');
    }
};
