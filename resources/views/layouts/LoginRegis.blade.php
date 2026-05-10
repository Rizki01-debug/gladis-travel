<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ setting('app_name', 'GLADIS') }}</title>

    {{-- BOOTSTRAP --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="icon" href="{{ setting('favicon') ? asset('storage/' . setting('favicon')) : asset('favicon.ico') }}">

    {{-- CUSTOM CSS --}}
    <link rel="stylesheet" href="{{ asset('css/loginregis.css') }}">
</head>

<body>

    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">

        <div class="col-md-6 col-lg-4">

            <div class="card shadow-lg border-0 auth-card">
                <div class="card-body p-4">

                    {{-- LOGO / APP NAME --}}
                    <div class="text-center mb-4">
                        <h4 class="auth-title">
                            {{ setting('app_name', 'GLADIS TRAVEL') }}
                        </h4>
                        <small class="text-muted">Sistem Travel Modern</small>
                    </div>

                    {{-- CONTENT --}}
                    <main>
                        @yield('content')
                    </main>

                </div>
            </div>

        </div>

    </div>

</body>

</html>