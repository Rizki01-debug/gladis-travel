<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MeetingPoint;
use App\Models\DepartureSchedule;

class City extends Model
{
    // ================= TABLE =================
    protected $table = 'cities';

    // ================= MASS ASSIGNMENT =================
    protected $fillable = [
        'name',
        'latitude',
        'longitude'
    ];

    // ================= CAST =================
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // ================= RELATIONS =================

    // 🔥 MEETING POINTS
    public function meetingPoints()
    {
        return $this->hasMany(MeetingPoint::class, 'city_id');
    }

    // 🔥 SCHEDULE (AS ORIGIN)
    public function originSchedules()
    {
        return $this->hasMany(DepartureSchedule::class, 'origin_city_id');
    }

    // 🔥 SCHEDULE (AS DESTINATION)
    public function destinationSchedules()
    {
        return $this->hasMany(DepartureSchedule::class, 'destination_city_id');
    }

    // ================= ACCESSOR =================

    // 🔥 STATUS LENGKAP
    public function getIsCompleteAttribute(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    // 🔥 FORMAT KOORDINAT
    public function getCoordinateAttribute(): string
    {
        return $this->is_complete
            ? "{$this->latitude}, {$this->longitude}"
            : '-';
    }

    // ================= HELPER =================

    // 🔥 SCOPE SEARCH
    public function scopeSearch($query, $keyword)
    {
        return $query->where('name', 'like', "%{$keyword}%");
    }
}
