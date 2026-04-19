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

        {{-- PRESET --}}
        @if ($theme && !str_starts_with($theme, '#'))
            <link rel="stylesheet" href="{{ asset('css/themes/' . $theme . '.css') }}">
        @endif

        {{-- CUSTOM --}}
        @if ($theme && str_starts_with($theme, '#'))
            <style>
                :root {
                    --primary-color: {{ $theme }};
                }
            </style>
        @endif
    @endauth

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />

    @stack('styles')
</head>

<body>

    {{-- ================= ALERT GLOBAL ================= --}}
    <div class="container mt-2">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
    </div>

    {{-- ================= SIDEBAR ================= --}}
    @auth
        @include('components.sidebar')
    @endauth

    {{-- ================= OVERLAY (MOBILE) ================= --}}
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    {{-- ================= MAIN ================= --}}
    <div class="main-wrapper">

        {{-- ================= NAVBAR ================= --}}
        @auth
            <div class="d-flex align-items-center">

                {{-- 🔥 BURGER BUTTON --}}
                <button id="sidebarToggle" class="btn btn-dark m-2 d-md-none">
                    ☰
                </button>

                <div class="flex-grow-1">
                    @include('components.navbar')
                </div>

            </div>
        @endauth

        {{-- ================= CONTENT ================= --}}
        <main class="p-3 p-md-4">
            @yield('content')
        </main>

    </div>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Leaflet -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

    {{-- ================= SIDEBAR TOGGLE ================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (toggle) {
                toggle.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                });
            }

            if (overlay) {
                overlay.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }
        });
    </script>

    @stack('scripts')
    @yield('scripts')

</body>

</html>