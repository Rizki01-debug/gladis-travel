<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Payment extends Model
{
    // ================= MASS ASSIGNMENT =================
    protected $fillable = [
        'booking_id',
        'order_id',
        'gross_amount',
        'payment_method',
        'status',
        'snap_token',
        'snap_url',
        'payment_details',
        'paid_at',
        'expired_at',
        'payment_channel',
        'bank',
        'va_number',
        'customer_details'
    ];

    // ================= CAST =================
    protected $casts = [
        'gross_amount' => 'decimal:2',
        'payment_details' => 'array',
        'customer_details' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // ================= RELATION =================

    /**
     * Relasi ke Booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // ================= ACCESSORS =================

    /**
     * Format harga dalam Rupiah
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->gross_amount ?? 0, 0, ',', '.');
    }

    /**
     * Label status dalam Bahasa Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'success' => 'Pembayaran Berhasil',
            'failed' => 'Pembayaran Gagal',
            'expired' => 'Kadaluarsa',
            'denied' => 'Ditolak',
            default => ucfirst($this->status ?? 'Unknown')
        };
    }

    /**
     * Warna status untuk Bootstrap
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'success' => 'success',
            'failed' => 'danger',
            'expired' => 'secondary',
            'denied' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Icon status
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'fa-clock',
            'success' => 'fa-check-circle',
            'failed' => 'fa-times-circle',
            'expired' => 'fa-hourglass-end',
            'denied' => 'fa-ban',
            default => 'fa-circle'
        };
    }

    /**
     * Nama metode pembayaran dalam Bahasa Indonesia
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'credit_card' => 'Kartu Kredit',
            'bank_transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'dana' => 'DANA',
            'linkaja' => 'LinkAja',
            default => $this->payment_method ?? 'Metode Lain'
        };
    }

    /**
     * Mendapatkan detail customer sebagai object
     */
    public function getCustomerAttribute(): ?object
    {
        return $this->customer_details ? (object) $this->customer_details : null;
    }

    /**
     * Mendapatkan nama customer
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->customer_details['name'] ?? 'Customer';
    }

    /**
     * Mendapatkan email customer
     */
    public function getCustomerEmailAttribute(): string
    {
        return $this->customer_details['email'] ?? 'customer@example.com';
    }

    /**
     * Mendapatkan phone customer
     */
    public function getCustomerPhoneAttribute(): string
    {
        return $this->customer_details['phone'] ?? '081234567890';
    }

    // ================= MUTATORS =================

    /**
     * Set customer details dari array
     */
    public function setCustomerDetailsAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['customer_details'] = $value;
        } else {
            $this->attributes['customer_details'] = json_encode($value);
        }
    }

    /**
     * Set payment details dari array
     */
    public function setPaymentDetailsAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['payment_details'] = $value;
        } else {
            $this->attributes['payment_details'] = json_encode($value);
        }
    }

    // ================= SCOPES =================

    /**
     * Scope untuk payment yang masih pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk payment yang berhasil
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope untuk payment yang gagal
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope untuk payment yang expired
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope untuk payment yang belum kadaluarsa
     */
    public function scopeNotExpired($query)
    {
        return $query->where('status', '!=', 'expired')
                     ->where('status', '!=', 'success');
    }

    /**
     * Scope untuk payment dengan order_id tertentu
     */
    public function scopeOrderId($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope untuk payment yang sudah melewati batas waktu
     */
    public function scopeExpiredAt($query)
    {
        return $query->where('expired_at', '<', now())
                     ->where('status', 'pending');
    }

    // ================= HELPER METHODS =================

    /**
     * Cek apakah payment masih pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Cek apakah payment berhasil
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Cek apakah payment gagal
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed' || $this->status === 'denied';
    }

    /**
     * Cek apakah payment expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Cek apakah payment sudah kadaluarsa secara waktu
     */
    public function isExpiredByTime(): bool
    {
        return $this->expired_at && $this->expired_at->isPast() && $this->isPending();
    }

    /**
     * Cek apakah payment bisa dibayar
     */
    public function isPayable(): bool
    {
        return $this->isPending() && !$this->isExpiredByTime();
    }

    /**
     * Mark payment sebagai success
     */
    public function markAsSuccess(): void
    {
        $this->update([
            'status' => 'success',
            'paid_at' => now()
        ]);
    }

    /**
     * Mark payment sebagai failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed'
        ]);
    }

    /**
     * Mark payment sebagai expired
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired'
        ]);
    }

    /**
     * Update status berdasarkan response Midtrans
     */
    public function updateStatusFromMidtrans(string $transactionStatus): void
    {
        $statusMap = [
            'capture' => 'success',
            'settlement' => 'success',
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'failed',
            'expire' => 'expired',
            'failure' => 'failed'
        ];

        $newStatus = $statusMap[$transactionStatus] ?? $this->status;

        if ($newStatus === 'success') {
            $this->markAsSuccess();
        } elseif ($newStatus === 'failed') {
            $this->markAsFailed();
        } elseif ($newStatus === 'expired') {
            $this->markAsExpired();
        } else {
            $this->update(['status' => $newStatus]);
        }
    }

    /**
     * Mendapatkan sisa waktu sebelum expired
     */
    public function getTimeRemainingAttribute(): ?string
    {
        if (!$this->expired_at || !$this->isPending()) {
            return null;
        }

        $diff = now()->diff($this->expired_at);
        
        if ($diff->invert) {
            return 'Kadaluarsa';
        }

        $parts = [];
        
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' jam';
        }
        
        if ($diff->i > 0) {
            $parts[] = $diff->i . ' menit';
        }
        
        if ($diff->s > 0 && empty($parts)) {
            $parts[] = $diff->s . ' detik';
        }

        return implode(' ', $parts);
    }

    /**
     * Check apakah payment memiliki Snap Token
     */
    public function hasSnapToken(): bool
    {
        return !empty($this->snap_token);
    }

    /**
     * Check apakah payment memiliki Snap URL
     */
    public function hasSnapUrl(): bool
    {
        return !empty($this->snap_url);
    }

    /**
     * Mendapatkan summary payment
     */
    public function getSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'booking_id' => $this->booking_id,
            'amount' => $this->formatted_amount,
            'status' => $this->status_label,
            'status_color' => $this->status_color,
            'method' => $this->payment_method_label,
            'paid_at' => $this->paid_at?->format('d M Y H:i'),
            'expired_at' => $this->expired_at?->format('d M Y H:i'),
            'time_remaining' => $this->time_remaining,
            'customer' => $this->customer_name,
            'booking' => $this->booking?->id ? [
                'id' => $this->booking->id,
                'departure_date' => $this->booking->departure_date?->format('d M Y'),
                'status' => $this->booking->status_label
            ] : null
        ];
    }

    // ================= BOOT =================

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Auto-expire payment yang sudah melewati batas waktu
        static::saving(function ($payment) {
            if ($payment->expired_at && $payment->expired_at->isPast() && $payment->status === 'pending') {
                $payment->status = 'expired';
            }
        });
    }
}