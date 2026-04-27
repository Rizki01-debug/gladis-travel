<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    // ================= MASS ASSIGNMENT =================
    protected $fillable = [
        'page_id',
        'key',
        'title',
        'content',
        'image',
        'extra',
        'order',
        'is_active',
    ];

    // ================= CAST =================
    protected $casts = [
        'extra'     => 'array',
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    // ================= DEFAULT VALUE =================
    protected $attributes = [
        'is_active' => true,
        'order'     => 0,
    ];

    // ================= RELATION =================
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    // ================= HELPER =================

    /**
     * Ambil data extra dengan aman
     */
    public function getExtra($key = null, $default = null)
    {
        if (!$this->extra) {
            return $default;
        }

        if ($key === null) {
            return $this->extra;
        }

        return $this->extra[$key] ?? $default;
    }

    /**
     * Ambil items repeater
     */
    public function getItems()
    {
        return $this->extra['items'] ?? [];
    }

    /**
     * Cek section aktif
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}