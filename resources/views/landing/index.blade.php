@extends('layouts.public')

@section('content')

@php
    $hero = $sections->firstWhere('key', 'hero');
    $dest = $sections->firstWhere('key', 'destinations');
    $schedule = $sections->firstWhere('key', 'schedule');
    $features = $sections->firstWhere('key', 'features');
    $testimonials = $sections->firstWhere('key', 'testimonials');
    $cta = $sections->firstWhere('key', 'cta');

    function extra($section, $key, $default = []) {
        return data_get($section, "extra.$key", $default);
    }

    function img($path) {
        if (empty($path)) return asset('default.jpg');
        if (str_starts_with($path, 'http')) return $path;
        return asset('storage/' . $path);
    }
@endphp

{{-- ================= HERO ================= --}}
@if($hero)
<section class="hero-section">

    <div class="hero-bg position-absolute w-100 h-100">
        <img src="{{ img($hero->image) }}">
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

{{-- ================= DESTINATIONS ================= --}}
@if($dest && $dest->is_active)
<section class="section" id="destinations">
    <div class="container">

        <div class="text-center mb-5">
            <h2 class="section-title">
                {{ $dest->title ?? 'Destinasi Populer' }}
            </h2>
        </div>

        <div class="row g-4">

            @forelse(extra($dest, 'items') as $item)
                <div class="col-md-6">

                    <div class="destination-card">

                        <img src="{{ img($item['image'] ?? null) }}">

                        <div class="p-4">
                            <h5 class="fw-bold">{{ $item['title'] ?? '-' }}</h5>

                            <p class="text-muted">
                                {{ $item['desc'] ?? '-' }}
                            </p>

                            <div class="text-primary fw-bold">
                                Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>

                    </div>

                </div>
            @empty
                <div class="text-center text-muted">
                    Belum ada data destinasi
                </div>
            @endforelse

        </div>

    </div>
</section>
@endif

{{-- ================= SCHEDULE ================= --}}
@if($schedule && $schedule->is_active)
<section class="section bg-light" id="schedule">
    <div class="container">

        <h2 class="section-title mb-4">
            {{ $schedule->title ?? 'Jadwal Keberangkatan' }}
        </h2>

        <div class="schedule-wrapper">

            @forelse(extra($schedule, 'items') as $item)
                <div class="schedule-item d-flex justify-content-between align-items-center flex-wrap">

                    <div>
                        <strong>{{ $item['destination'] ?? '-' }}</strong><br>
                        <small class="text-muted">{{ $item['date'] ?? '-' }}</small>
                    </div>

                    <span class="badge-success-custom">
                        {{ $item['status'] ?? '-' }}
                    </span>

                    <a href="{{ route('login') }}" class="btn btn-primary-custom btn-sm">
                        Booking
                    </a>

                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    Belum ada jadwal tersedia
                </div>
            @endforelse

        </div>

    </div>
</section>
@endif

{{-- ================= FEATURES ================= --}}
@if($features && $features->is_active)
<section class="section" id="features">
    <div class="container">

        <div class="row text-center">

            @forelse(extra($features, 'items') as $item)
                <div class="col-md-4 mb-4">

                    <div class="feature-box">
                        <div class="feature-icon mx-auto">
                            <i class="{{ $item['icon'] }}"></i>
                        </div>

                        <h5 class="fw-bold">{{ $item['title'] ?? '-' }}</h5>

                        <p class="text-muted">
                            {{ $item['desc'] ?? '-' }}
                        </p>
                    </div>

                </div>
            @empty
                <div class="text-center text-muted">
                    Belum ada fitur
                </div>
            @endforelse

        </div>

    </div>
</section>
@endif

{{-- ================= TESTIMONIAL ================= --}}
@if($testimonials && $testimonials->is_active)
<section class="testimonial-section section">
    <div class="container">

        <h2 class="text-center mb-5 text-white fw-bold">
            Testimonials
        </h2>

        <div class="row">

           @forelse(extra($testimonials, 'items') as $item)
    <div class="col-md-4 mb-4">

        <div class="testimonial-card h-100">

            {{-- TESTIMONIAL TEXT --}}
            <p class="fst-italic mb-4">
                "{{ $item['text'] ?? '-' }}"
            </p>

            {{-- USER --}}
            <div class="d-flex align-items-center gap-3">

                {{-- IMAGE --}}
                <img 
                    src="{{ img($item['image'] ?? null) }}"
                    alt="{{ $item['name'] ?? 'User' }}"
                    class="testimonial-avatar">

                {{-- NAME --}}
                <div>
                    <div class="fw-bold">
                        {{ $item['name'] ?? '-' }}
                    </div>

                    <small class="text-light opacity-75">
                        Penumpang GLADIS
                    </small>
                </div>

            </div>

        </div>

    </div>
@empty
    <div class="text-center text-white">
        Belum ada testimonial
    </div>
@endforelse

        </div>

    </div>
</section>
@endif

{{-- ================= CTA ================= --}}
@if($cta && $cta->is_active)
<section class="cta-section">

    <div class="container">

        <h2>
            {{ $cta->title ?? 'Siap Berangkat?' }}
        </h2>

        <p class="mb-4">
            {{ $cta->content ?? 'Pesan sekarang dan nikmati perjalanan nyaman bersama kami.' }}
        </p>

        <a href="{{ route('login') }}" class="btn btn-light btn-lg">
            {{ extra($cta, 'button_text', 'Pesan Sekarang') }}
        </a>

    </div>

</section>
@endif

@endsection