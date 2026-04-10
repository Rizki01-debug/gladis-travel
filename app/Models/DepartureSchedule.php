<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RoutePoint; // 🔥 WAJIB

class DepartureSchedule extends Model
{
    protected $fillable = [
        'vehicle_id',
        'origin_city_id',
        'destination_city_id',
        'departure_time'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function origin()
    {
        return $this->belongsTo(City::class, 'origin_city_id');
    }

    public function destination()
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }

    public function routePoints()
    {
        return $this->hasMany(RoutePoint::class, 'schedule_id')
                    ->orderBy('order');
    }
}