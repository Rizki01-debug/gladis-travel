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

    // 🔥 USER (yang booking)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔥 SCHEDULE (jadwal perjalanan)
    public function schedule()
    {
        return $this->belongsTo(DepartureSchedule::class, 'schedule_id');
    }

    // 🔥 MULTI SEAT (pivot booking_seats)
    public function seats()
    {
        return $this->belongsToMany(
            Seat::class,
            'booking_seats',
            'booking_id',
            'seat_id'
        )->withTimestamps(); // 🔥 biar future aman
    }

    // 🔥 MEETING POINT (kalau pilih meeting point)
    public function meetingPoint()
    {
        return $this->belongsTo(MeetingPoint::class);
    }
}