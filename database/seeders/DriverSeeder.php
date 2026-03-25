<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Driver',
            'email' => 'driver@gladis.com',
            'password' => Hash::make('password'),
            'role_id' => 3
        ]);
    }
}
