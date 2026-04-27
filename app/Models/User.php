<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ================= MASS ASSIGNMENT =================
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'theme_color'
    ];

    // ================= HIDDEN =================
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ================= CAST =================
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ================= RELATION =================

    // 🔥 ROLE
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // 🔥 DRIVER → VEHICLES (INI BARU 🔥)
    public function vehicles()
    {
        return $this->hasOne(Vehicle::class, 'driver_id');
    }

    // 🔥 DRIVER → EARNINGS
    public function earnings()
    {
        return $this->hasMany(DriverEarning::class, 'driver_id');
    }

    // 🔥 PASSENGER → BOOKINGS (INI JUGA PENTING 🔥)
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // ================= HELPER ROLE =================

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
