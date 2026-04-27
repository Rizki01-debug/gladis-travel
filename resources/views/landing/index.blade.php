@extends('layouts.public')

@section('content')

@php
    $hero = $sections->firstWhere('key', 'hero');
    $dest = $sections->firstWhere('key', 'destinations');
    $schedule = $sections->firstWhere('key', 'schedule');
    $features = $sections->firstWhere('key', 'features');
    $testimonials = $sections->firstWhere('key', 'testimonials');
    $cta = $sections->firstWhere('key', 'cta'); // 🔥 TAMBAHAN

    function extra($section, $key, $default = []) {
        return data_get($section, "extra.$key", $default);
    }

    function img($path) {
        if (empty($path)) return asset('default.jpg');
        if (str_starts_with($path, 'http')) return $path;

        return asset('storage/' . $path);
    }
@endphp

{{-- ================= NAVBAR ================= --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            @if(setting('logo'))
                <img src="{{ img(setting('logo')) }}" height="40">
            @endif

            <span class="fw-bold text-primary">
                {{ setting('app_name', 'GLADIS') }}
            </span>
        </a>

        <div class="ms-auto">
            <a href="{{ route('login') }}" class="btn btn-primary">
                Login
            </a>
        </div>

    </div>
</nav>

{{-- ================= HERO ================= --}}
@if($hero)
<section class="vh-100 d-flex align-items-center text-white position-relative"
    style="background: url('{{ img($hero->image) }}') center/cover;">

    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>

    <div class="container text-center position-relative">
        <h1 class="display-4 fw-bold mb-3">
            {{ $hero->title ?? 'Travel Mudah & Nyaman' }}
        </h1>

        <p class="lead mb-4">
            {{ $hero->content ?? 'Pesan travel dengan cepat dan nyaman.' }}
        </p>

        <a href="{{ route('login') }}" class="btn btn-lg btn-primary">
            {{ extra($hero, 'button_text', 'Mulai Sekarang') }}
        </a>
    </div>
</section>
@endif

{{-- ================= DESTINATIONS ================= --}}
@if($dest && $dest->is_active)
<section class="py-5">
    <div class="container">

        <h2 class="fw-bold mb-4 text-center">
            {{ $dest->title ?? 'Destinasi Populer' }}
        </h2>

        <div class="row g-4">
            @forelse(extra($dest, 'items') as $item)
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">

                        <img src="{{ img($item['image'] ?? null) }}"
                             class="card-img-top"
                             style="height:200px;object-fit:cover;">

                        <div class="card-body">
                            <h5 class="fw-bold">{{ $item['title'] ?? '-' }}</h5>
                            <p class="text-muted">{{ $item['desc'] ?? '-' }}</p>

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
<section class="py-5 bg-light">
    <div class="container">

        <h2 class="fw-bold mb-4">
            {{ $schedule->title ?? 'Jadwal Keberangkatan' }}
        </h2>

        <div class="card shadow-sm">
            @forelse(extra($schedule, 'items') as $item)
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom flex-wrap">

                    <div>
                        <strong>{{ $item['destination'] ?? '-' }}</strong><br>
                        <small class="text-muted">{{ $item['date'] ?? '-' }}</small>
                    </div>

                    <span class="badge bg-success">
                        {{ $item['status'] ?? '-' }}
                    </span>

                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                        Booking
                    </a>

                </div>
            @empty
                <div class="p-3 text-center text-muted">
                    Belum ada jadwal tersedia
                </div>
            @endforelse
        </div>

    </div>
</section>
@endif

{{-- ================= FEATURES ================= --}}
@if($features && $features->is_active)
<section class="py-5">
    <div class="container">

        <div class="row text-center">
            @forelse(extra($features, 'items') as $item)
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold">{{ $item['title'] ?? '-' }}</h5>
                    <p class="text-muted">{{ $item['desc'] ?? '-' }}</p>
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
<section class="py-5 bg-primary text-white">
    <div class="container">

        <h2 class="fw-bold text-center mb-4">
            Testimonials
        </h2>

        <div class="row">
            @forelse(extra($testimonials, 'items') as $item)
                <div class="col-md-4 mb-3">
                    <div class="bg-white text-dark p-4 rounded shadow-sm h-100">

                        <p class="fst-italic">
                            "{{ $item['text'] ?? '-' }}"
                        </p>

                        <strong>{{ $item['name'] ?? '-' }}</strong>

                    </div>
                </div>
            @empty
                <div class="text-center">
                    Belum ada testimonial
                </div>
            @endforelse
        </div>

    </div>
</section>
@endif

{{-- ================= CTA (🔥 FINAL FIX) ================= --}}
@if($cta && $cta->is_active)
<section class="py-5 text-white text-center"
    style="background: linear-gradient(135deg, #0d6efd, #6610f2);">

    <div class="container">

        <h2 class="fw-bold mb-3">
            {{ $cta->title ?? 'Siap Berangkat?' }}
        </h2>

        <p class="mb-4">
            {{ $cta->content ?? 'Pesan sekarang dan nikmati perjalanan nyaman bersama kami.' }}
        </p>

        <a href="{{ route('login') }}" class="btn btn-light btn-lg fw-semibold">
            {{ extra($cta, 'button_text', 'Pesan Sekarang') }}
        </a>

    </div>
</section>
@endif

@endsection