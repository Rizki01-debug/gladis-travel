<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Database\Seeders\TariffSeeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DriverSeeder;
use Database\Seeders\PassengerSeeder;
use Database\Seeders\FeatureSeeder;
use Database\Seeders\VehicleSeeder;
use Database\Seeders\SeatSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SuperAdminSeeder::class,
            AdminSeeder::class,
            DriverSeeder::class,
            PassengerSeeder::class,
            TariffSeeder::class,
            FeatureSeeder::class,
            VehicleSeeder::class,
            SeatSeeder::class,
        ]);
    }
}
