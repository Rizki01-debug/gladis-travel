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
        'order' => 'integer',
    ];

    // ================= RELATION =================

    // 🔥 MEETING POINT
    public function meetingPoint()
    {
        return $this->belongsTo(MeetingPoint::class, 'meeting_point_id');
    }

    // 🔥 SCHEDULE
    public function schedule()
    {
        return $this->belongsTo(DepartureSchedule::class, 'schedule_id');
    }

    // ================= SCOPE =================

    // 🔥 URUTAN AMAN (hindari conflict reserved keyword)
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    // 🔥 WITH RELATION (ANTI N+1 QUERY)
    public function scopeWithPoint($query)
    {
        return $query->with('meetingPoint');
    }

    // ================= ACCESSOR =================

    // 🔥 LATITUDE
    public function getLatitudeAttribute()
    {
        return (float) ($this->meetingPoint?->latitude ?? 0);
    }

    // 🔥 LONGITUDE
    public function getLongitudeAttribute()
    {
        return (float) ($this->meetingPoint?->longitude ?? 0);
    }

    // 🔥 NAMA TITIK
    public function getNameAttribute()
    {
        return $this->meetingPoint?->name ?? '-';
    }

    // 🔥 COORDINATE (🔥 PENTING BUAT MAP)
    public function getCoordinateAttribute()
    {
        if (!$this->meetingPoint) {
            return null;
        }

        return [
            'lat' => (float) $this->meetingPoint->latitude,
            'lng' => (float) $this->meetingPoint->longitude,
        ];
    }

    // ================= HELPER =================

    // 🔥 VALID POINT (BIAR GA ERROR DI MAP)
    public function isValid(): bool
    {
        return $this->meetingPoint &&
            $this->meetingPoint->latitude &&
            $this->meetingPoint->longitude;
    }
}
