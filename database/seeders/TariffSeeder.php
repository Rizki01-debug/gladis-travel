<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tariff;

class TariffSeeder extends Seeder
{
    public function run(): void
    {
        Tariff::create([
            'name' => 'Default',
            'price_per_km' => 1500
        ]);
    }
}