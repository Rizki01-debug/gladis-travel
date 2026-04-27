<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        // 🔥 SAFE RESET
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Feature::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ================= MASTER FEATURE =================
        $features = [
            'dashboard',
            'users',
            'vehicles',
            'cities',
            'meeting_points',
            'schedules',
            'tariffs',

            // CORE
            'booking',
            'driver',
            'trip',
            'earnings',

            // FINANCE
            'finance',
            'laporan',
            'setoran', // 🔥 TAMBAHAN

            // SYSTEM
            'activity_logs',
            'features',
        ];

        // ================= ROLE =================
        $roles = [
            'super_admin',
            'admin',
            'driver',
            'passenger'
        ];

        // ================= DEFAULT ACCESS =================
        $defaultAccess = [

            // 🔥 FULL ACCESS
            'super_admin' => $features,

            // 🔥 ADMIN
            'admin' => [
                'dashboard',
                'vehicles',
                'cities',
                'meeting_points',
                'schedules',
                'tariffs',
                'finance',
                'laporan',
                'setoran',
            ],

            // 🔥 DRIVER
            'driver' => [
                'dashboard',
                'driver',
                'trip',
                'earnings',
            ],

            // 🔥 PASSENGER
            'passenger' => [
                'booking',
            ],
        ];

        // ================= INSERT =================
        foreach ($roles as $role) {

            foreach ($features as $feature) {

                Feature::create([
                    'name' => $feature,
                    'role' => $role,
                    'is_active' => in_array($feature, $defaultAccess[$role]),
                ]);
            }
        }
    }
}