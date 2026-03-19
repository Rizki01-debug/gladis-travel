<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingPoint extends Model
{
    protected $fillable = [
        'city_id',
        'name',
        'address',
        'google_maps_link'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
