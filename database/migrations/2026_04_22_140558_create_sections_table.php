<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();

            // 🔗 RELASI KE PAGE
            $table->foreignId('page_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // 🧠 IDENTITAS
            $table->string('key')->index(); // hero, destinations, dll
            $table->string('title')->nullable();

            // 📝 KONTEN
            $table->text('content')->nullable();

            // 🖼️ MEDIA
            $table->string('image')->nullable(); // 🔥 WAJIB (buat hero, dll)

            // 🧩 FLEX DATA (advanced CMS)
            $table->json('extra')->nullable();

            // 🔢 URUTAN
            $table->integer('order')->default(0);

            // 🔥 STATUS AKTIF
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};