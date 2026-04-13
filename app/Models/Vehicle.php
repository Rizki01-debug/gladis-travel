<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'name',
        'plate_number',
        'seat_capacity',
        'status',
        'driver_id' // 🔥 WAJIB TAMBAH
    ];

    // ================= RELATION =================

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function schedules()
    {
        return $this->hasMany(DepartureSchedule::class);
    }
}
