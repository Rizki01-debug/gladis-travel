<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DepartureSchedule extends Model
{
    // ================= MASS ASSIGNMENT =================
    protected $fillable = [
        'vehicle_id',
        'origin_city_id',
        'destination_city_id',
        'departure_time'
    ];

    // ================= CAST =================
    protected $casts = [
        // 🔥 BIARKAN STRING (AMAN UNTUK TIME)
        'departure_time' => 'string'
    ];

    // ================= RELATION =================

    // 🔥 Vehicle
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // 🔥 Origin City
    public function origin()
    {
        return $this->belongsTo(City::class, 'origin_city_id');
    }

    // 🔥 Destination City
    public function destination()
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }

    // 🔥 Bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }

    // 🔥 Route Points (URUT)
    public function routePoints()
    {
        return $this->hasMany(RoutePoint::class, 'schedule_id')
            ->orderBy('order');
    }

    // ================= HELPER =================

    // 🔥 GET JARAK (SAFE)
    public function getDistanceKmAttribute()
    {
        try {
            return calculateRouteDistance($this->routePoints);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    // 🔥 FORMAT JAM (LEBIH FLEXIBLE)
    public function getFormattedTimeAttribute()
    {
        if (!$this->departure_time) return '-';

        try {
            // handle H:i:s dari DB
            return Carbon::createFromFormat('H:i:s', $this->departure_time)->format('H:i');
        } catch (\Exception $e) {
            try {
                // handle H:i dari input
                return Carbon::createFromFormat('H:i', $this->departure_time)->format('H:i');
            } catch (\Exception $e) {
                return $this->departure_time;
            }
        }
    }
}