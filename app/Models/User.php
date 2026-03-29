<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'theme_color' // 🔥 wajib
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 🔗 RELASI
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // 🔐 HELPER ROLE
    public function isSuperAdmin(): bool
    {
        return $this->role_id === 1;
    }

    public function isAdmin(): bool
    {
        return $this->role_id === 2;
    }

    public function isDriver(): bool
    {
        return $this->role_id === 3;
    }

    public function isPassenger(): bool
    {
        return $this->role_id === 4;
    }
}
