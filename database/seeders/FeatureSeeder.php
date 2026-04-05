<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        Feature::truncate(); // 🔥 reset biar clean

        $mapping = [

            // ================= SUPER ADMIN =================
            'super_admin' => [
                'vehicles',
                'cities',
                'meeting_points',
                'schedules',
                'tariffs',
                'finance',
                'laporan'
            ],

            // ================= ADMIN =================
            'admin' => [
                'vehicles',
                'meeting_points',
                'schedules',
                'finance',
                'laporan'
            ],

            // ================= DRIVER =================
            'driver' => [
                'driver',
                'trip'
            ],

            // ================= PASSENGER =================
            'passenger' => [
                'booking'
            ],
        ];

        foreach ($mapping as $role => $features) {
            foreach ($features as $feature) {
                Feature::create([
                    'name' => $feature,
                    'role' => $role,
                    'is_active' => true
                ]);
            }
        }
    }
}