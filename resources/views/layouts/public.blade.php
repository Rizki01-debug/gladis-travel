<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- TITLE --}}
    <title>{{ setting('app_name', 'GLADIS') }}</title>

    {{-- FAVICON --}}
    <link rel="icon" href="{{ setting('favicon') ? asset('storage/' . setting('favicon')) : asset('favicon.ico') }}">

    {{-- FONT --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- BOOTSTRAP --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- CUSTOM CSS --}}
    <link rel="stylesheet" href="{{ asset('css/landing_page.css') }}">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .main-content {
            padding-top: 90px;
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- ================= NAVBAR ================= --}}
    <nav class="navbar navbar-expand-lg fixed-top navbar-custom">
        <div class="container">

            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                @if (setting('logo'))
                    <img src="{{ asset('storage/' . setting('logo')) }}" height="40">
                @endif

                <span>{{ setting('app_name', 'GLADIS') }}</span>
            </a>

            <div class="ms-auto">
                @if (auth()->check())
                    <a href="{{ route('dashboard.user') }}" class="btn btn-primary-custom">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary-custom">
                        Login
                    </a>
                @endif
            </div>

        </div>
    </nav>

    {{-- ================= CONTENT ================= --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- ================= FOOTER (FINAL) ================= --}}
    <footer class="footer-custom">
        <div class="container">

            <div class="row gy-4">

                {{-- BRAND --}}
                <div class="col-md-4">
                    <div class="footer-brand">
                        {{ setting('app_name', 'GLADIS TRAVEL') }}
                    </div>

                    <p class="footer-desc">
                        {{ setting('footer_text', 'Travel terbaik untuk perjalanan nyaman, aman, dan terpercaya.') }}
                    </p>
                </div>

                {{-- MENU --}}
                <div class="col-md-4 footer-links">
                    <div class="fw-bold mb-3">Menu</div>

                    <a href="/">Beranda</a>
                    <a href="#">Jadwal</a>
                </div>

                {{-- KONTAK --}}
                <div class="col-md-4">
                    <div class="fw-bold mb-3">Kontak</div>

                    <p class="footer-desc mb-1">
                        {{ setting('contact_email', '-') }}
                    </p>

                    <p class="footer-desc">
                        {{ setting('contact_phone', '-') }}
                    </p>
                </div>

            </div>

            {{-- DIVIDER --}}
            <div class="footer-divider"></div>

            {{-- BOTTOM --}}
            <div class="text-center footer-bottom">
                {{ setting('copyright', '© 2026 GLADIS TRAVEL') }}
            </div>

        </div>
    </footer>

    {{-- JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

</body>
</html>