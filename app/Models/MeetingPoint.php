<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingPoint extends Model
{
    protected $fillable = [
        'city_id',
        'name',
        'address',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // ================= RELATION =================

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // 🔥 PENTING (UNTUK DELETE CHECK)
    public function routePoints()
    {
        return $this->hasMany(RoutePoint::class, 'meeting_point_id');
    }

    // ================= ACCESSOR =================

    public function getCoordinateAttribute()
    {
        if (!$this->latitude || !$this->longitude) {
            return '-';
        }

        return "{$this->latitude}, {$this->longitude}";
    }

    // ================= SCOPE =================

    public function scopeSearch($query, $keyword)
    {
        return $query->where('name', 'like', "%{$keyword}%");
    }
}
