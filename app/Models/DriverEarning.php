<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverEarning extends Model
{
    protected $fillable = [
        'driver_id',
        'booking_id',
        'amount',
        'status'
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
