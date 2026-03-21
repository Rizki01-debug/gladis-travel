<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingSeat extends Model
{
    protected $fillable = [
        'booking_id',
        'seat_id'
    ];

    // (biar whereHas jalan)
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}