<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seat;
use App\Models\Vehicle;

class SeatSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = Vehicle::all();

        if ($vehicles->isEmpty()) {
            $this->command->warn('⚠️ Tidak ada kendaraan!');
            return;
        }

        foreach ($vehicles as $vehicle) {

            // 🔥 HAPUS DULU BIAR TIDAK DOUBLE
            Seat::where('vehicle_id', $vehicle->id)->delete();

            // 🔥 LOOP SESUAI KAPASITAS
            for ($i = 1; $i <= $vehicle->seat_capacity; $i++) {

                Seat::create([
                    'vehicle_id' => $vehicle->id,
                    'seat_number' => $i,

                    // 🔥 INI KUNCI UTAMA
                    'is_driver_seat' => $i === 1
                ]);
            }
        }

        $this->command->info('✅ SeatSeeder berhasil dijalankan!');
    }
}