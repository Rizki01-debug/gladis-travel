<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\Section;

class LandingSeeder extends Seeder
{
    public function run(): void
    {
        // ================= PAGE =================
        $page = Page::updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Landing Page',
                'is_active' => true,
            ]
        );

        // ================= HERO =================
        Section::updateOrCreate(
            [
                'page_id' => $page->id,
                'key' => 'hero'
            ],
            [
                'title' => 'Travel Mudah & Nyaman',
                'content' => 'Pesan travel GLADIS lebih cepat, aman, nyaman, dan terpercaya menuju berbagai kota di Jawa Barat.',
                'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?w=1600',
                'order' => 1,
                'is_active' => true,
            ]
        );

        // ================= DESTINATIONS =================
        Section::updateOrCreate(
            [
                'page_id' => $page->id,
                'key' => 'destinations'
            ],
            [
                'title' => 'Destinasi Populer',
                'content' => 'Nikmati perjalanan terbaik bersama GLADIS Travel.',
                'extra' => [
                    'items' => [
                        [
                            'title' => 'Bandung',
                            'desc' => 'Perjalanan nyaman menuju Kota Bandung.',
                            'price' => 150000,
                            'image' => 'https://images.unsplash.com/photo-1527631746610-bca00a040d60?w=800'
                        ],
                        [
                            'title' => 'Jakarta',
                            'desc' => 'Travel harian menuju Jakarta.',
                            'price' => 180000,
                            'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800'
                        ],
                        [
                            'title' => 'Cirebon',
                            'desc' => 'Perjalanan cepat menuju Kota Cirebon.',
                            'price' => 80000,
                            'image' => 'https://images.unsplash.com/photo-1514565131-fce0801e5785?w=800'
                        ]
                    ]
                ],
                'order' => 2,
                'is_active' => true,
            ]
        );

        // ================= SCHEDULE =================
        Section::updateOrCreate(
            [
                'page_id' => $page->id,
                'key' => 'schedule'
            ],
            [
                'title' => 'Jadwal Keberangkatan',
                'content' => 'Keberangkatan setiap hari.',
                'extra' => [
                    'items' => [
                        [
                            'destination' => 'Bandung',
                            'date' => 'Setiap Hari 07.00 WIB',
                            'status' => 'Tersedia'
                        ],
                        [
                            'destination' => 'Jakarta',
                            'date' => 'Setiap Hari 13.00 WIB',
                            'status' => 'Tersedia'
                        ],
                        [
                            'destination' => 'Cirebon',
                            'date' => 'Setiap Hari 19.00 WIB',
                            'status' => 'Tersedia'
                        ]
                    ]
                ],
                'order' => 3,
                'is_active' => true,
            ]
        );

        // ================= FEATURES =================
        Section::updateOrCreate(
            [
                'page_id' => $page->id,
                'key' => 'features'
            ],
            [
                'title' => 'Mengapa Memilih Kami',
                'content' => '',
                'extra' => [
                    'items' => [
                        [
                            'title' => 'Booking Online',
                            'desc' => 'Pesan kursi kapan saja.',
                            'icon' => 'fas fa-ticket-alt'
                        ],
                        [
                            'title' => 'Driver Profesional',
                            'desc' => 'Pengemudi berpengalaman dan ramah.',
                            'icon' => 'fas fa-user-tie'
                        ],
                        [
                            'title' => 'Perjalanan Aman',
                            'desc' => 'Kendaraan nyaman dan terawat.',
                            'icon' => 'fas fa-shield-alt'
                        ]
                    ]
                ],
                'order' => 4,
                'is_active' => true,
            ]
        );

        // ================= TESTIMONIAL =================
        Section::updateOrCreate(
            [
                'page_id' => $page->id,
                'key' => 'testimonials'
            ],
            [
                'title' => 'Testimonial',
                'content' => '',
                'extra' => [
                    'items' => [
                        [
                            'name' => 'Andi',
                            'text' => 'Pelayanan sangat cepat dan nyaman.',
                            'image' => 'https://randomuser.me/api/portraits/men/10.jpg'
                        ],
                        [
                            'name' => 'Siti',
                            'text' => 'Driver ramah dan perjalanan aman.',
                            'image' => 'https://randomuser.me/api/portraits/women/22.jpg'
                        ],
                        [
                            'name' => 'Rizki',
                            'text' => 'Booking online sangat mudah.',
                            'image' => 'https://randomuser.me/api/portraits/men/45.jpg'
                        ]
                    ]
                ],
                'order' => 5,
                'is_active' => true,
            ]
        );

        // ================= CTA =================
        Section::updateOrCreate(
            [
                'page_id' => $page->id,
                'key' => 'cta'
            ],
            [
                'title' => 'Siap Berangkat Bersama GLADIS?',
                'content' => 'Pesan kursi sekarang dan nikmati perjalanan yang aman, nyaman, dan tepat waktu.',
                'extra' => [
                    'button_text' => 'Booking Sekarang'
                ],
                'order' => 6,
                'is_active' => true,
            ]
        );
    }
}