<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\Section;

class LandingSeeder extends Seeder
{
    public function run(): void
    {
        // 🔥 PAGE
        $page = Page::create([
            'title' => 'Landing Page',
            'slug' => 'home',
            'is_active' => 1
        ]);

        // 🔥 HERO
        Section::create([
            'page_id' => $page->id,
            'key' => 'hero',
            'title' => 'Travel Mudah & Nyaman',
            'content' => 'Pesan travel sekarang lebih cepat, aman, dan terpercaya.',
            'image' => 'https://picsum.photos/1200/600',
            'order' => 1,
            'is_active' => 1
        ]);

        // 🔥 DESTINATIONS
        Section::create([
            'page_id' => $page->id,
            'key' => 'destinations',
            'title' => 'Destinasi Populer',
            'content' => 'Bandung, Jakarta, Indramayu, Cirebon',
            'order' => 2,
            'is_active' => 1
        ]);

        // 🔥 SCHEDULE
        Section::create([
            'page_id' => $page->id,
            'key' => 'schedule',
            'title' => 'Jadwal Keberangkatan',
            'content' => 'Setiap hari tersedia keberangkatan pagi & malam',
            'order' => 3,
            'is_active' => 1
        ]);

        // 🔥 CTA
        Section::create([
            'page_id' => $page->id,
            'key' => 'cta',
            'title' => 'Pesan Sekarang',
            'content' => 'Klik tombol di bawah untuk booking kursi',
            'order' => 4,
            'is_active' => 1
        ]);
    }
}