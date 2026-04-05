<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            // 🔥 RELASI ROLE (AMAN)
            $table->foreignId('role_id')
                ->default(4) // 🔥 auto passenger
                ->constrained('roles')
                ->restrictOnDelete();

            $table->string('name');

            $table->string('email')->unique();

            // tambahan
            $table->string('phone')->nullable();

            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            // 🔥 tema UI
            $table->string('theme_color')->default('#0d6efd');

            $table->rememberToken();

            $table->timestamps();

            // 🔥 OPTIMASI
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};