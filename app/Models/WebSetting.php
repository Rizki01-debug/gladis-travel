<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebSetting extends Model
{
    protected $fillable = [
        'app_name',
        'logo',
        'favicon',
        'footer_text',
        'copyright',
        'contact_email',
        'contact_phone',
    ];
}