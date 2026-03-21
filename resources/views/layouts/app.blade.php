<!DOCTYPE html>
<html>

<head>
    <title>GLADIS Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
</head>

<body>

    <div class="d-flex">

        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="flex-grow-1">

            <!-- Navbar -->
            @include('components.navbar')

            <!-- Content -->
            <div class="p-4">
                @yield('content')
            </div>

        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
    @yield('scripts')
</body>

</html>
