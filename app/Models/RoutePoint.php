<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutePoint extends Model
{
    protected $fillable = [
        'schedule_id',
        'meeting_point_id',
        'order'
    ];

    public function meetingPoint()
    {
        return $this->belongsTo(MeetingPoint::class);
    }

    public function schedule()
    {
        return $this->belongsTo(DepartureSchedule::class, 'schedule_id');
    }
}