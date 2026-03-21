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
        'distance_km',        // 🔥 tambah ini
        'price_estimation',   // 🔥 tambah ini
        'status'
    ];
}
