<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('payment_id')
                  ->nullable()
                  ->after('booking_id')
                  ->constrained('payments')
                  ->nullOnDelete();
            
            $table->timestamp('paid_at')
                  ->nullable()
                  ->after('status');
            
            $table->string('description')
                  ->nullable()
                  ->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn(['payment_id', 'paid_at', 'description']);
        });
    }
};