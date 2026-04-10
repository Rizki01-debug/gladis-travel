<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingPoint extends Model
{
    protected $fillable = [
        'city_id',
        'name',
        'address',
        'latitude',   // 🔥 WAJIB
        'longitude'   // 🔥 WAJIB
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
