@extends('layouts.public')

@section('content')
    @php
        $hero = $sections->firstWhere('key', 'hero');
        $dest = $sections->firstWhere('key', 'destinations');
        $schedule = $sections->firstWhere('key', 'schedule');
        $features = $sections->firstWhere('key', 'features');
        $testimonials = $sections->firstWhere('key', 'testimonials');
        $cta = $sections->firstWhere('key', 'cta');

        /**
         * Helper untuk mengambil data dari extra dengan default value
         */
        function extra($section, $key, $default = [])
        {
            if (!$section) {
                return $default;
            }
            return data_get($section, "extra.$key", $default);
        }

        /**
         * Helper untuk menampilkan image dengan fallback
         */
        function img($path)
        {
            if (empty($path)) {
                return asset('images/default.jpg');
            }

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            if (str_starts_with($path, 'storage/') || str_starts_with($path, '/storage/')) {
                return asset($path);
            }

            if (str_starts_with($path, 'sections/') || str_starts_with($path, 'temp/')) {
                return asset('storage/' . $path);
            }

            return asset('storage/' . $path);
        }

        /**
         * Helper untuk format harga Rupiah
         */
        function rupiah($amount)
        {
            if (empty($amount)) {
                return 'Rp 0';
            }
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }

        /**
         * Helper untuk truncate text
         */
        function truncate($text, $length = 100)
        {
            if (empty($text)) {
                return '-';
            }
            if (strlen($text) <= $length) {
                return $text;
            }
            return substr($text, 0, $length) . '...';
        }
    @endphp

    {{-- ================= HERO ================= --}}
    @if ($hero && $hero->is_active)
        <section class="hero-section">

            <div class="hero-bg position-absolute w-100 h-100">
                @if (!empty($hero->image))
                    <img src="{{ img($hero->image) }}" alt="{{ $hero->title ?? 'Hero Image' }}" loading="lazy"
                        onerror="this.style.display='none'; this.parentElement.querySelector('.hero-fallback').style.display='flex';">
                    <div class="hero-fallback bg-dark"
                        style="display:none; width:100%; height:100%; align-items:center; justify-content:center;">
                        <i class="fas fa-image text-white-50" style="font-size: 64px;"></i>
                    </div>
                @else
                    <div class="bg-secondary w-100 h-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-image text-white-50" style="font-size: 64px;"></i>
                    </div>
                @endif
                <div class="hero-overlay"></div>
            </div>

            <div class="container position-relative">
                <div class="row align-items-center">

                    <div class="col-lg-6 hero-content">
                        <h1>
                            {{ $hero->title ?? 'Travel Mudah & Nyaman' }}
                        </h1>

                        <p class="mt-3 mb-4">
                            {{ $hero->content ?? 'Pesan travel dengan cepat dan nyaman.' }}
                        </p>

                        <a href="{{ route('login') }}" class="btn btn-primary-custom">
                            {{ extra($hero, 'button_text', 'Mulai Sekarang') }}
                        </a>
                    </div>

                </div>
            </div>

        </section>
    @endif

    {{-- ================= DESTINATIONS - FIXED ================= --}}
    @if ($dest && $dest->is_active)
        <section class="section" id="destinations" style="padding: 60px 0;">
            <div class="container">

                <div class="text-center mb-5">
                    <h2 class="section-title" style="font-size: 32px; font-weight: 700; color: #2d3748;">
                        {{ $dest->title ?? 'Destinasi Populer' }}
                    </h2>
                    @if (!empty($dest->content))
                        <p class="text-muted" style="font-size: 18px; color: #718096;">
                            {{ $dest->content }}
                        </p>
                    @endif
                </div>

                <div class="row g-4">

                    @php
                        $destItems = extra($dest, 'items', []);
                        // Filter items yang memiliki data
                        $destItems = array_filter($destItems, function ($item) {
                            return !empty($item['title']) || !empty($item['desc']);
                        });
                        // Re-index array setelah filter
                        $destItems = array_values($destItems);
                    @endphp

                    @forelse($destItems as $index => $item)
                        <div class="col-md-6 col-lg-4 mb-4">

                            <div class="destination-card"
                                style="background: #ffffff; 
                                border-radius: 12px; 
                                overflow: hidden; 
                                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                                transition: transform 0.3s ease, box-shadow 0.3s ease;
                                height: 100%;">

                                {{-- IMAGE SECTION --}}
                                @if (!empty($item['image']))
                                    <div class="destination-image-wrapper"
                                        style="position: relative; overflow: hidden; height: 220px;">
                                        <img src="{{ img($item['image']) }}" alt="{{ $item['title'] ?? 'Destination' }}"
                                            class="destination-image"
                                            style="width: 100%; 
                                            height: 100%; 
                                            object-fit: cover;
                                            transition: transform 0.3s ease;"
                                            loading="lazy"
                                            onerror="this.style.display='none'; this.parentElement.querySelector('.destination-placeholder').style.display='flex';">
                                        <div class="destination-placeholder"
                                            style="display: none; 
                                            width: 100%; 
                                            height: 100%; 
                                            background: #f7fafc; 
                                            align-items: center; 
                                            justify-content: center;
                                            flex-direction: column;">
                                            <i class="fas fa-image"
                                                style="font-size: 48px; color: #cbd5e0; margin-bottom: 10px;"></i>
                                            <span style="color: #a0aec0; font-size: 14px;">Gambar tidak tersedia</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="destination-placeholder"
                                        style="width: 100%; 
                                        height: 220px; 
                                        background: #f7fafc; 
                                        display: flex; 
                                        align-items: center; 
                                        justify-content: center;
                                        flex-direction: column;">
                                        <i class="fas fa-image"
                                            style="font-size: 48px; color: #cbd5e0; margin-bottom: 10px;"></i>
                                        <span style="color: #a0aec0; font-size: 14px;">Belum ada gambar</span>
                                    </div>
                                @endif

                                {{-- CONTENT SECTION --}}
                                <div class="p-4" style="padding: 20px;">
                                    {{-- TITLE --}}
                                    <h5 class="fw-bold"
                                        style="font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 8px;">
                                        {{ $item['title'] ?? 'Destinasi' }}
                                    </h5>

                                    {{-- DESCRIPTION --}}
                                    <p class="text-muted"
                                        style="font-size: 14px; color: #718096; margin-bottom: 12px; min-height: 40px;">
                                        {{ !empty($item['desc']) ? truncate($item['desc'], 80) : 'Nikmati perjalanan nyaman menuju destinasi pilihan Anda.' }}
                                    </p>

                                    {{-- PRICE --}}
                                    <div class="text-primary fw-bold"
                                        style="color: #0d6efd !important; font-size: 18px; font-weight: 700;">
                                        {{ rupiah($item['price'] ?? 0) }}
                                    </div>

                                    {{-- DATE (optional) --}}
                                    @if (!empty($item['date']))
                                        <small class="text-muted d-block mt-2" style="color: #a0aec0; font-size: 12px;">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            {{ $item['date'] }}
                                        </small>
                                    @endif

                                    {{-- BOOKING BUTTON --}}
                                    <a href="{{ route('login') }}" class="btn btn-primary-custom btn-sm mt-3"
                                        style="display: inline-block;
                                      padding: 6px 20px;
                                      background: #0d6efd;
                                      color: #ffffff;
                                      border-radius: 6px;
                                      text-decoration: none;
                                      font-size: 13px;
                                      font-weight: 600;
                                      transition: background 0.3s ease;">
                                        Pesan Sekarang
                                    </a>
                                </div>

                            </div>

                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center text-muted py-5" style="padding: 60px 0;">
                                <i class="fas fa-map-marked-alt fa-3x mb-3" style="font-size: 48px; color: #cbd5e0;"></i>
                                <p style="color: #718096;">Belum ada data destinasi</p>
                            </div>
                        </div>
                    @endforelse

                </div>

            </div>
        </section>
    @endif

    {{-- ================= SCHEDULE ================= --}}
    @if ($schedule && $schedule->is_active)
        <section class="section bg-light" id="schedule">
            <div class="container">

                <div class="text-center mb-4">
                    <h2 class="section-title">
                        {{ $schedule->title ?? 'Jadwal Keberangkatan' }}
                    </h2>
                    @if (!empty($schedule->content))
                        <p class="text-muted">{{ $schedule->content }}</p>
                    @endif
                </div>

                <div class="schedule-wrapper">

                    @php
                        $scheduleItems = extra($schedule, 'items', []);
                        $scheduleItems = array_filter($scheduleItems, function ($item) {
                            return !empty($item['destination']);
                        });
                    @endphp

                    @forelse($scheduleItems as $item)
                        <div
                            class="schedule-item d-flex justify-content-between align-items-center flex-wrap p-3 border-bottom bg-white rounded mb-2">

                            <div>
                                <strong>{{ $item['destination'] ?? '-' }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $item['date'] ?? '-' }}
                                </small>
                            </div>

                            <div class="d-flex align-items-center gap-3 mt-2 mt-sm-0">
                                <span
                                    class="badge {{ ($item['status'] ?? 'Tersedia') == 'Tersedia' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $item['status'] ?? 'Tersedia' }}
                                </span>

                                <a href="{{ route('login') }}" class="btn btn-primary-custom btn-sm">
                                    Booking
                                </a>
                            </div>

                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="far fa-calendar-alt fa-3x mb-3"></i>
                            <p>Belum ada jadwal tersedia</p>
                        </div>
                    @endforelse

                </div>

            </div>
        </section>
    @endif

    {{-- ================= FEATURES ================= --}}
    @if ($features && $features->is_active)
        <section class="section" id="features">
            <div class="container">

                <div class="text-center mb-5">
                    <h2 class="section-title">
                        {{ $features->title ?? 'Fitur Unggulan' }}
                    </h2>
                    @if (!empty($features->content))
                        <p class="text-muted">{{ $features->content }}</p>
                    @endif
                </div>

                <div class="row g-4">

                    @php
                        $featureItems = extra($features, 'items', []);
                        $featureItems = array_filter($featureItems, function ($item) {
                            return !empty($item['title']);
                        });
                    @endphp

                    @forelse($featureItems as $item)
                        <div class="col-md-4 mb-4">

                            <div class="feature-box h-100 p-4 text-center">
                                <div class="feature-icon mx-auto mb-3">
                                    @if (!empty($item['icon']))
                                        <i class="{{ $item['icon'] }}"></i>
                                    @else
                                        <i class="fas fa-star"></i>
                                    @endif
                                </div>

                                <h5 class="fw-bold">{{ $item['title'] ?? '-' }}</h5>

                                <p class="text-muted">
                                    {{ $item['desc'] ?? '-' }}
                                </p>
                            </div>

                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-cogs fa-3x mb-3"></i>
                                <p>Belum ada fitur</p>
                            </div>
                        </div>
                    @endforelse

                </div>

            </div>
        </section>
    @endif

    {{-- ================= TESTIMONIAL - FIXED ================= --}}
    @if ($testimonials && $testimonials->is_active)
        <section class="testimonial-section section"
            style="padding: 60px 0; background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);">
            <div class="container">

                <div class="text-center mb-5">
                    <h2 class="text-white fw-bold" style="font-size: 32px; font-weight: 700;">
                        {{ $testimonials->title ?? 'Testimonials' }}
                    </h2>
                    @if (!empty($testimonials->content))
                        <p class="text-white-50" style="font-size: 18px; opacity: 0.75;">
                            {{ $testimonials->content }}
                        </p>
                    @endif
                </div>

                <div class="row g-4">

                    @php
                        $testimonialItems = extra($testimonials, 'items', []);
                        $testimonialItems = array_filter($testimonialItems, function ($item) {
                            return !empty($item['name']) || !empty($item['text']);
                        });
                        $testimonialItems = array_values($testimonialItems);
                    @endphp

                    @forelse($testimonialItems as $item)
                        <div class="col-md-4 mb-4">

                            <div class="testimonial-card h-100 p-4"
                                style="background: rgba(255,255,255,0.08);
                                backdrop-filter: blur(10px);
                                border-radius: 16px;
                                border: 1px solid rgba(255,255,255,0.1);
                                transition: transform 0.3s ease, box-shadow 0.3s ease;
                                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                                display: flex;
                                flex-direction: column;">

                                {{-- QUOTE ICON --}}
                                <div style="margin-bottom: 12px;">
                                    <i class="fas fa-quote-left"
                                        style="color: rgba(255,255,255,0.15); font-size: 24px;"></i>
                                </div>

                                {{-- TESTIMONIAL TEXT --}}
                                <p class="fst-italic mb-4"
                                    style="color: #e2e8f0; font-size: 15px; line-height: 1.6; flex: 1;">
                                    "{{ $item['text'] ?? 'Pengalaman perjalanan yang luar biasa dengan GLADIS Travel.' }}"
                                </p>

                                {{-- USER SECTION --}}
                                <div class="d-flex align-items-center gap-3" style="margin-top: auto;">

                                    {{-- AVATAR - FIXED: Check if image exists in storage --}}
                                    @php
                                        $hasImage =
                                            !empty($item['image']) && Storage::disk('public')->exists($item['image']);
                                    @endphp

                                    @if ($hasImage)
                                        <img src="{{ img($item['image']) }}" alt="{{ $item['name'] ?? 'User' }}"
                                            class="testimonial-avatar rounded-circle"
                                            style="width: 60px; 
                                           height: 60px; 
                                           object-fit: cover; 
                                           border: 2px solid rgba(255,255,255,0.2);
                                           flex-shrink: 0;"
                                            loading="lazy">
                                    @else
                                        {{-- FALLBACK AVATAR --}}
                                        <div class="testimonial-avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 60px; 
                                            height: 60px; 
                                            background: linear-gradient(135deg, #4a5568, #2d3748);
                                            border: 2px solid rgba(255,255,255,0.2);
                                            flex-shrink: 0;">
                                            <i class="fas fa-user text-white" style="font-size: 24px; opacity: 0.5;"></i>
                                        </div>
                                    @endif

                                    {{-- USER INFO --}}
                                    <div style="flex: 1; min-width: 0;">
                                        <div class="fw-bold text-white"
                                            style="font-size: 16px; font-weight: 600; margin-bottom: 2px;">
                                            {{ $item['name'] ?? 'Pengguna GLADIS' }}
                                        </div>

                                        <small class="text-light opacity-75" style="font-size: 12px; display: block;">
                                            <i class="fas fa-user-check me-1" style="font-size: 10px;"></i>
                                            {{ $item['role'] ?? 'Penumpang GLADIS' }}
                                        </small>

                                        @if (!empty($item['date']))
                                            <small class="text-light opacity-50"
                                                style="font-size: 11px; display: block; margin-top: 2px;">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                {{ $item['date'] }}
                                            </small>
                                        @endif
                                    </div>

                                </div>

                                {{-- RATING --}}
                                @if (!empty($item['rating']))
                                    <div class="mt-3" style="display: flex; gap: 4px;">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star"
                                                style="color: {{ $i <= $item['rating'] ? '#fbbf24' : 'rgba(255,255,255,0.15)' }};
                                              font-size: 14px;"></i>
                                        @endfor
                                    </div>
                                @endif

                            </div>

                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center text-white py-5" style="padding: 60px 0;">
                                <i class="fas fa-comment fa-3x mb-3 opacity-50" style="color: rgba(255,255,255,0.3);"></i>
                                <p style="color: rgba(255,255,255,0.6);">Belum ada testimonial</p>
                            </div>
                        </div>
                    @endforelse

                </div>

            </div>
        </section>
    @endif

    {{-- ================= CTA ================= --}}
    @if ($cta && $cta->is_active)
        <section class="cta-section">
            <div class="container text-center">

                <h2 class="fw-bold mb-3">
                    {{ $cta->title ?? 'Siap Berangkat?' }}
                </h2>

                <p class="mb-4 lead">
                    {{ $cta->content ?? 'Pesan sekarang dan nikmati perjalanan nyaman bersama kami.' }}
                </p>

                <a href="{{ route('login') }}" class="btn btn-light btn-lg px-5">
                    {{ extra($cta, 'button_text', 'Pesan Sekarang') }}
                </a>

                @if (!empty(extra($cta, 'sub_text')))
                    <p class="mt-3 text-light opacity-75">
                        <small>{{ extra($cta, 'sub_text') }}</small>
                    </p>
                @endif

            </div>
        </section>
    @endif

    {{-- ================= HAPUS DEBUG INFO ================= --}}
    {{-- DEBUG INFO TELAH DIHAPUS --}}
@endsection
