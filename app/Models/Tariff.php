<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = [
        'name',
        'base_price',
        'price_per_km',
        'pickup_fee',
        'min_price',
        'max_price'
    ];
}
