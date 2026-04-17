<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutePoint extends Model
{
    // ================= TABLE =================
    protected $table = 'route_points';

    // ================= MASS ASSIGNMENT =================
    protected $fillable = [
        'schedule_id',
        'meeting_point_id',
        'order'
    ];

    // ================= CAST =================
    protected $casts = [
        'order' => 'integer'
    ];

    // ================= RELATION =================

    // 🔥 RELASI KE MEETING POINT
    public function meetingPoint()
    {
        return $this->belongsTo(MeetingPoint::class, 'meeting_point_id');
    }

    // 🔥 RELASI KE SCHEDULE
    public function schedule()
    {
        return $this->belongsTo(DepartureSchedule::class, 'schedule_id');
    }

    // ================= SCOPE =================

    // 🔥 URUTKAN BERDASARKAN ORDER
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ================= HELPER =================

    // 🔥 AMBIL LAT LNG LANGSUNG (biar gampang dipakai)
    public function getLatitudeAttribute()
    {
        return $this->meetingPoint?->latitude;
    }

    public function getLongitudeAttribute()
    {
        return $this->meetingPoint?->longitude;
    }

    // 🔥 NAMA TITIK
    public function getNameAttribute()
    {
        return $this->meetingPoint?->name ?? '-';
    }
}