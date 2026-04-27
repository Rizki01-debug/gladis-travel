<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Section;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'is_active',
    ];

    // 🔥 RELATION: Page → Sections
    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    // 🔥 HELPER AMBIL SECTION BERDASARKAN KEY
    public function getSection($key)
    {
        return $this->sections->firstWhere('key', $key);
    }
}