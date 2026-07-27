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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke bookings
            $table->foreignId('booking_id')
                  ->constrained('bookings')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            // Identifikasi unik untuk Midtrans
            $table->string('order_id')
                  ->unique()
                  ->comment('Order ID yang dikirim ke Midtrans');
            
            // Detail pembayaran
            $table->decimal('gross_amount', 15, 2)
                  ->comment('Total pembayaran');
            
            $table->string('payment_method')
                  ->nullable()
                  ->comment('credit_card, bank_transfer, qris, dll');
            
            // Status pembayaran
            $table->enum('status', [
                'pending', 
                'success', 
                'failed', 
                'expired',
                'denied'
            ])->default('pending');
            
            // Midtrans response data
            $table->string('snap_token')
                  ->nullable()
                  ->comment('Token untuk Snap popup');
            
            $table->string('snap_url')
                  ->nullable()
                  ->comment('URL redirect ke Midtrans');
            
            $table->json('payment_details')
                  ->nullable()
                  ->comment('Detail response dari Midtrans');
            
            // Timestamp tracking
            $table->timestamp('paid_at')
                  ->nullable()
                  ->comment('Waktu pembayaran berhasil');
            
            $table->timestamp('expired_at')
                  ->nullable()
                  ->comment('Batas waktu pembayaran');
            
            // Metadata tambahan
            $table->string('payment_channel')
                  ->nullable()
                  ->comment('Channel pembayaran spesifik');
            
            $table->string('bank')
                  ->nullable()
                  ->comment('Bank tujuan transfer');
            
            $table->string('va_number')
                  ->nullable()
                  ->comment('Nomor Virtual Account');
            
            $table->json('customer_details')
                  ->nullable()
                  ->comment('Detail customer untuk keperluan transaksi');
            
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['booking_id', 'status']);
            $table->index('order_id');
            $table->index('status');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};