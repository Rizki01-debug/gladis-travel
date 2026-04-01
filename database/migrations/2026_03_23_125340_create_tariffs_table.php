<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();

            // 🔥 Nama tarif
            $table->string('name')->default('Tarif Default');

            // 🔥 harga dasar (meeting point)
            $table->decimal('base_price', 10, 2)->default(50000);

            // 🔥 harga per km (GIS)
            $table->decimal('price_per_km', 10, 2)->default(3000);

            $table->decimal('pickup_fee', 10, 2)->default(5000);
            // 🔥 optional (future scaling)
            $table->decimal('min_price', 10, 2)->nullable(); // minimal charge
            $table->decimal('max_price', 10, 2)->nullable(); // batas maksimal

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};