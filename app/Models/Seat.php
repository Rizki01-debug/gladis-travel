<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'vehicle_id',
        'seat_number',
        'is_driver_seat'
    ];

    protected $casts = [
        'is_driver_seat' => 'boolean',
    ];
}
