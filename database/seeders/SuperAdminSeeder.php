<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gladis.com',
            'phone' => '08123456789',
            'role_id' => 1, // super_admin
            'password' => Hash::make('password'),
        ]);
    }
}