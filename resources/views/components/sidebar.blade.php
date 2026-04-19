@php
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();
    $theme = $theme ?? '#1e293b';

    // 🔥 helper active (biar rapi, ga ngulang)
    function active($route)
    {
        return request()->routeIs($route) ? 'active bg-light text-dark fw-semibold' : 'text-white';
    }
@endphp

@if ($user)

    {{-- ================= SIDEBAR ================= --}}
    <div id="sidebar" class="sidebar text-white" style="background: {{ $theme }};">

        <div class="p-3">

            {{-- LOGO --}}
            <h4 class="mb-4">🚐 GLADIS</h4>

            <ul class="nav flex-column">

                {{-- ================= DASHBOARD ================= --}}
                <li class="nav-item mb-2">
                    @if ($user->isSuperAdmin())
                        <a href="{{ route('superadmin.dashboard') }}"
                            class="nav-link {{ active('superadmin.dashboard') }}">
                            🏠 Dashboard
                        </a>
                    @elseif ($user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ active('admin.dashboard') }}">
                            🏠 Dashboard
                        </a>
                    @elseif ($user->isDriver())
                        <a href="{{ route('driver.dashboard') }}" class="nav-link {{ active('driver.dashboard') }}">
                            🏠 Dashboard
                        </a>
                    @elseif ($user->isPassenger())
                        <a href="{{ route('dashboard.user') }}"
                            class="nav-link {{ request()->routeIs('dashboard.user') ? 'active' : '' }}">
                            🏠 Dashboard
                        </a>
                    @endif
                </li>

                {{-- ================= SUPER ADMIN ================= --}}
                @if ($user->isSuperAdmin())
                    <small class="text-white-50 mt-2">MASTER DATA</small>

                    <a href="{{ route('vehicles.index') }}" class="nav-link {{ active('vehicles.*') }}">🚐 Vehicles</a>
                    <a href="{{ route('cities.index') }}" class="nav-link {{ active('cities.*') }}">🏙 Cities</a>
                    <a href="{{ route('meeting-points.index') }}" class="nav-link {{ active('meeting-points.*') }}">📍
                        Meeting Points</a>
                    <a href="{{ route('schedules.index') }}" class="nav-link {{ active('schedules.*') }}">🗓
                        Schedules</a>
                    <a href="{{ route('tariffs.index') }}" class="nav-link {{ active('tariffs.*') }}">💸 Tarif</a>
                    <a href="{{ route('users.index') }}" class="nav-link {{ active('users.*') }}">👤 User
                        Management</a>

                    <small class="text-white-50 mt-3">SYSTEM</small>

                    <a href="{{ route('activity.index') }}" class="nav-link {{ active('activity.*') }}">📜 Activity
                        Log</a>
                    <a href="{{ route('features.index') }}" class="nav-link {{ active('features.*') }}">⚙ Kelola
                        Fitur</a>
                @endif

                {{-- ================= ADMIN ================= --}}
                @if ($user->isAdmin())
                    <small class="text-white-50 mt-3">MASTER DATA</small>

                    <a href="{{ route('vehicles.index') }}" class="nav-link {{ active('vehicles.*') }}">🚐
                        Kendaraan</a>
                    <a href="{{ route('meeting-points.index') }}" class="nav-link {{ active('meeting-points.*') }}">📍
                        Meeting Point</a>
                    <a href="{{ route('schedules.index') }}" class="nav-link {{ active('schedules.*') }}">🗓
                        Jadwal</a>
                @endif

                {{-- ================= FINANCE ================= --}}
                @if ($user->isAdmin() || $user->isSuperAdmin())
                    <small class="text-white-50 mt-3">FINANCE</small>

                    <a href="{{ route('finance.index') }}" class="nav-link {{ active('finance.index') }}">💰
                        Dashboard</a>
                    <a href="{{ route('finance.report') }}" class="nav-link {{ active('finance.report') }}">📊
                        Laporan</a>
                    <a href="{{ route('finance.setoran') }}" class="nav-link {{ active('finance.setoran') }}">💳
                        Setoran Driver</a>
                @endif

                {{-- ================= DRIVER ================= --}}
                @if ($user->isDriver())
                    <small class="text-white-50 mt-3">DRIVER</small>

                    <a href="{{ route('driver.index') }}" class="nav-link {{ active('driver.index') }}">📥 Booking
                        Masuk</a>
                    <a href="{{ route('driver.trips') }}" class="nav-link {{ active('driver.trips') }}">🚗 Trip
                        Saya</a>
                    <a href="{{ route('driver.earnings') }}" class="nav-link {{ active('driver.earnings') }}">💰
                        Earnings</a>
                @endif

                {{-- ================= PASSENGER ================= --}}
                @if ($user->isPassenger())
                    <small class="text-white-50 mt-3">BOOKING</small>

                    <a href="{{ route('booking.index') }}" class="nav-link {{ active('booking.index') }}">🎫
                        Booking</a>
                    <a href="{{ route('booking.my') }}" class="nav-link {{ active('booking.my') }}">📋 Booking
                        Saya</a>
                @endif

                {{-- ================= SETTINGS ================= --}}
                <li class="mt-4">
                    <a href="{{ route('settings.index') }}" class="nav-link {{ active('settings.index') }}">
                        ⚙ Settings
                    </a>
                </li>

            </ul>
        </div>
    </div>

    {{-- ================= OVERLAY MOBILE ================= --}}
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

@endif
