<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'schedule_id',
        'departure_date',
        'pickup_type',
        'meeting_point_id',
        'pickup_maps',
        'distance_km',
        'price_estimation',
        'status'
    ];

    // ================= RELATION =================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(DepartureSchedule::class, 'schedule_id');
    }

    // 🔥 INI YANG KAMU KURANGIN
    public function seats()
    {
        return $this->belongsToMany(Seat::class, 'booking_seats');
    }
}