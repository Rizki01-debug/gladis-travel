<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    // ================= MASS ASSIGNMENT =================
    protected $fillable = [
        'name',
        'plate_number',
        'seat_capacity',
        'status',
        'driver_id'
    ];

    // ================= CAST =================
    protected $casts = [
        'seat_capacity' => 'integer',
    ];

    // ================= RELATION =================

    // 🔥 DRIVER
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // 🔥 SCHEDULE
    public function schedules()
    {
        return $this->hasMany(DepartureSchedule::class);
    }

    // 🔥 SEATS
    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    // ================= HELPER =================

    // 🔥 NAMA + PLAT (BIAR ENAK DI DROPDOWN)
    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->plate_number})";
    }
}