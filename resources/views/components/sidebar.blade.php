<div class="bg-dark text-white p-3" style="width: 250px; min-height: 100vh;">
    <h4 class="mb-4">GLADIS</h4>

    <ul class="nav flex-column">

        {{-- DASHBOARD --}}
        <li class="nav-item">
            @if(auth()->user()->role_id == 1)
                <a href="{{ route('superadmin.dashboard') }}" class="nav-link text-white">Dashboard</a>
            @elseif(auth()->user()->role_id == 2)
                <a href="{{ route('admin.dashboard') }}" class="nav-link text-white">Dashboard</a>
            @elseif(auth()->user()->role_id == 3)
                <a href="{{ route('driver.dashboard') }}" class="nav-link text-white">Dashboard</a>
            @elseif(auth()->user()->role_id == 4)
                <a href="{{ route('booking.index') }}" class="nav-link text-white">Dashboard</a>
            @endif
        </li>

        {{-- ================= SUPER ADMIN ================= --}}
        @if(auth()->user()->role_id == 1)

            <li class="nav-item"><a href="{{ route('vehicles.index') }}" class="nav-link text-white">Vehicles</a></li>
            <li class="nav-item"><a href="{{ route('cities.index') }}" class="nav-link text-white">Cities</a></li>
            <li class="nav-item"><a href="{{ route('meeting-points.index') }}" class="nav-link text-white">Meeting Points</a></li>
            <li class="nav-item"><a href="{{ route('schedules.index') }}" class="nav-link text-white">Schedules</a></li>

        @endif

        {{-- ================= ADMIN ================= --}}
        @if(auth()->user()->role_id == 2)

            <li class="nav-item"><a href="{{ route('finance.index') }}" class="nav-link text-white">Finance</a></li>
            <li class="nav-item"><a href="{{ route('finance.report') }}" class="nav-link text-white">Laporan</a></li>

        @endif

        {{-- ================= DRIVER ================= --}}
        @if(auth()->user()->role_id == 3)

            <li class="nav-item"><a href="{{ route('driver.index') }}" class="nav-link text-white">Jadwal</a></li>

        @endif

        {{-- ================= PASSENGER ================= --}}
        @if(auth()->user()->role_id == 4)

            <li class="nav-item"><a href="{{ route('booking.index') }}" class="nav-link text-white">Booking</a></li>

        @endif

    </ul>
</div>