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
        Schema::create('features', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // contoh: finance, booking, schedules
            $table->string('role'); // admin, driver, passenger, super_admin

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // 🔥 biar tidak duplicate
            $table->unique(['name', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
