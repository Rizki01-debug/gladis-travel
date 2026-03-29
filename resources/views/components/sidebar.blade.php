@php
    /** @var \App\Models\User $user */
    $user = auth()->user();
@endphp

<div class="sidebar p-3"
     style="width:250px; min-height:100vh; background-color: {{ $user->theme_color }}; color:white;">

    <h4 class="mb-4">GLADIS</h4>

    <ul class="nav flex-column">

        {{-- DASHBOARD --}}
        <li class="nav-item">
            @if ($user->isSuperAdmin())
                <a href="{{ route('superadmin.dashboard') }}" class="nav-link text-white">Dashboard</a>
            @elseif ($user->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="nav-link text-white">Dashboard</a>
            @elseif ($user->isDriver())
                <a href="{{ route('driver.dashboard') }}" class="nav-link text-white">Dashboard</a>
            @elseif ($user->isPassenger())
                <a href="{{ route('booking.index') }}" class="nav-link text-white">Dashboard</a>
            @endif
        </li>

        {{-- ================= SUPER ADMIN ================= --}}
        @if ($user->isSuperAdmin())
            <li class="nav-item"><a href="{{ route('vehicles.index') }}" class="nav-link text-white">Vehicles</a></li>
            <li class="nav-item"><a href="{{ route('cities.index') }}" class="nav-link text-white">Cities</a></li>
            <li class="nav-item"><a href="{{ route('meeting-points.index') }}" class="nav-link text-white">Meeting Points</a></li>
            <li class="nav-item"><a href="{{ route('schedules.index') }}" class="nav-link text-white">Schedules</a></li>
        @endif

        {{-- ================= ADMIN ================= --}}
        @if ($user->isAdmin())
            <li class="nav-item"><a href="{{ route('finance.index') }}" class="nav-link text-white">Finance</a></li>
            <li class="nav-item"><a href="{{ route('finance.report') }}" class="nav-link text-white">Laporan</a></li>
        @endif

        {{-- ================= DRIVER ================= --}}
        @if ($user->isDriver())
            <li class="nav-item"><a href="{{ route('driver.index') }}" class="nav-link text-white">Jadwal</a></li>
        @endif

        {{-- ================= PASSENGER ================= --}}
        @if ($user->isPassenger())
            <li class="nav-item"><a href="{{ route('booking.index') }}" class="nav-link text-white">Booking</a></li>
        @endif

        {{-- ================= SETTINGS ================= --}}
        <li class="nav-item mt-3">
            <a href="{{ route('settings.index') }}" class="nav-link text-white">⚙ Settings</a>
        </li>

    </ul>
</div>