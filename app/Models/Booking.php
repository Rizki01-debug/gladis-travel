<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    // ================= MASS ASSIGNMENT =================
    protected $fillable = [
        'user_id',
        'schedule_id',
        'departure_date',
        'pickup_type',
        'meeting_point_id',
        'pickup_maps',
        'phone',
        'distance_km',
        'price_estimation',
        'status'
    ];

    // ================= CAST =================
    protected $casts = [
        'departure_date' => 'date',
        'distance_km' => 'float',
        'price_estimation' => 'float'
    ];

    // ================= RELATION =================

    // 🔥 USER
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔥 TRIP
    public function trip()
    {
        return $this->hasOne(Trip::class);
    }

    // 🔥 SCHEDULE
    public function schedule()
    {
        return $this->belongsTo(DepartureSchedule::class, 'schedule_id');
    }

    // 🔥 MULTI SEAT
    public function seats()
    {
        return $this->belongsToMany(
            Seat::class,
            'booking_seats',
            'booking_id',
            'seat_id'
        )->withTimestamps();
    }

    // 🔥 MEETING POINT
    public function meetingPoint()
    {
        return $this->belongsTo(MeetingPoint::class);
    }

    // 🔥 DRIVER EARNING
    public function driverEarning()
    {
        return $this->hasOne(DriverEarning::class);
    }

    // ================= HELPER =================

    // 🔥 FORMAT TANGGAL (AMAN)
    public function getFormattedDateAttribute()
    {
        try {
            return $this->departure_date
                ? Carbon::parse($this->departure_date)->format('d M Y')
                : '-';
        } catch (\Throwable $e) {
            return $this->departure_date ?? '-';
        }
    }

    // 🔥 FORMAT HARGA (🔥 TAMBAHAN PENTING)
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price_estimation ?? 0, 0, ',', '.');
    }

    // 🔥 STATUS LABEL
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status)
        };
    }

    // 🔥 STATUS COLOR (Bootstrap)
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    // 🔥 CEK BISA CANCEL
    public function canBeCancelled(): bool
    {
        return $this->status === 'pending';
    }
}