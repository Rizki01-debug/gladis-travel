<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- ================= TITLE ================= --}}
    <title>{{ setting('app_name', 'GLADIS') }}</title>

    {{-- ================= FAVICON (FIX STORAGE) ================= --}}
    <link rel="icon" href="{{ setting('favicon') ? asset('storage/'.setting('favicon')) : asset('favicon.ico') }}">

    {{-- ================= GOOGLE FONT ================= --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- ================= BOOTSTRAP ================= --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- ================= CUSTOM CSS ================= --}}
    <link rel="stylesheet" href="{{ asset('css/landing_page.css') }}">

    {{-- ================= GLOBAL STYLE ================= --}}
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
        }

        /* Navbar */
        .navbar-custom {
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.9);
            transition: 0.3s;
        }

        .navbar-brand img {
            height: 40px;
        }

        /* Footer */
        .footer-custom {
            background: #111827;
            color: #fff;
        }

        /* Spacing fix */
        .main-content {
            padding-top: 90px;
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- ================= NAVBAR ================= --}}
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm navbar-custom">
        <div class="container">

            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                
                {{-- 🔥 LOGO DINAMIS --}}
                @if(setting('logo'))
                    <img src="{{ asset('storage/'.setting('logo')) }}">
                @endif

                <span class="fw-bold text-primary">
                    {{ setting('app_name', 'GLADIS') }}
                </span>
            </a>

            <div class="ms-auto">
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                    Login
                </a>
            </div>

        </div>
    </nav>

    {{-- ================= CONTENT ================= --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- ================= FOOTER ================= --}}
    <footer class="footer-custom py-5 mt-5">
        <div class="container text-center">

            {{-- FOOTER TEXT --}}
            <p class="text-muted mb-2">
                {{ setting('footer_text', 'Travel terbaik untuk perjalanan Anda') }}
            </p>

            {{-- COPYRIGHT --}}
            <p class="mb-2">
                {{ setting('copyright', '© 2026 GLADIS') }}
            </p>

            {{-- CONTACT --}}
            <small class="text-muted">
                {{ setting('contact_email', '-') }} |
                {{ setting('contact_phone', '-') }}
            </small>

        </div>
    </footer>

    {{-- ================= JS ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

</body>
</html>