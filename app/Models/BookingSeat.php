<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingSeat extends Model
{
    protected $fillable = [
        'booking_id',
        'seat_id'
    ];

    // 🔥 RELASI KE BOOKING
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // 🔥 RELASI KE SEAT (PENTING)
    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
}