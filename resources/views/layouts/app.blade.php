<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GLADIS Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    {{-- ================= THEME SYSTEM ================= --}}
    @auth
        @php
            $theme = auth()->user()->theme_color;
        @endphp

        {{-- ✅ PRESET THEME (blue, green, dll) --}}
        @if ($theme && !str_starts_with($theme, '#'))
            <link rel="stylesheet" href="{{ asset('css/themes/' . $theme . '.css') }}">
        @endif

        {{-- ✅ CUSTOM COLOR --}}
        @if ($theme && str_starts_with($theme, '#'))
            <style>
                :root {
                    --primary-color: {{ $theme }};
                }
            </style>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
    @endauth

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />

    @stack('styles')
</head>

<body>

    <div class="d-flex">

        {{-- Sidebar --}}
        @auth
            @include('components.sidebar')
        @endauth

        {{-- Main Content --}}
        <div class="flex-grow-1">

            {{-- Navbar --}}
            @auth
                @include('components.navbar')
            @endauth

            {{-- Content --}}
            <main class="p-4">
                @yield('content')
            </main>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

    @stack('scripts')
    @yield('scripts')

</body>

</html>
