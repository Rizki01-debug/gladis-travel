<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name'];

    public function meetingPoints()
    {
        return $this->hasMany(MeetingPoint::class);
    }
}
