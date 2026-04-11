<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude'
    ];

    // ================= RELATION =================
    public function meetingPoints()
    {
        return $this->hasMany(MeetingPoint::class);
    }

    // 🔥 OPTIONAL (biar rapih di view)
    public function getIsCompleteAttribute()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }
}