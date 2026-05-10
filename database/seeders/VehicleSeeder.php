<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\User;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        // ================= AMBIL DRIVER =================
        $drivers = User::where('role_id', 3)->get();

        if ($drivers->isEmpty()) {
            $this->command->warn('⚠️ Tidak ada driver ditemukan!');
            return;
        }

        // ================= DATA KENDARAAN =================
        $vehicles = [
            [
                'name' => 'Avanza',
                'plate_number' => 'E 1234 AA',
                'seat_capacity' => 8,
            ],
        ];

        foreach ($vehicles as $index => $data) {

            // 🔥 ROTASI DRIVER (biar adil)
            $driver = $drivers[$index % $drivers->count()];

            Vehicle::create([
                'name' => $data['name'],
                'plate_number' => $data['plate_number'],
                'seat_capacity' => $data['seat_capacity'],
                'status' => 'active',
                'driver_id' => $driver->id
            ]);
        }

        $this->command->info('✅ VehicleSeeder berhasil dijalankan!');
    }
}