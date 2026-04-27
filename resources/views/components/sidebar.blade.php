@php
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();
    $theme = $theme ?? '#1e293b';

    function canAccess($feature)
    {
        return featureActive($feature);
    }

    function active($route)
    {
        return request()->routeIs($route)
            ? 'active bg-light text-dark fw-semibold'
            : 'text-white';
    }
@endphp

@if ($user)

<div id="sidebar" class="sidebar text-white" style="background: {{ $theme }};">
    <div class="p-3">

        <h4 class="mb-4">{{ setting('app_name', 'GLADIS') }}</h4>

        <ul class="nav flex-column">

            {{-- ================= DASHBOARD ================= --}}
            @if (canAccess('dashboard'))
                <li class="nav-item mb-2">
                    @if ($user->isSuperAdmin())
                        <a href="{{ route('superadmin.dashboard') }}" class="nav-link {{ active('superadmin.dashboard') }}">🏠 Dashboard</a>
                    @elseif ($user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ active('admin.dashboard') }}">🏠 Dashboard</a>
                    @elseif ($user->isDriver())
                        <a href="{{ route('driver.dashboard') }}" class="nav-link {{ active('driver.dashboard') }}">🏠 Dashboard</a>
                    @elseif ($user->isPassenger())
                        <a href="{{ route('dashboard.user') }}" class="nav-link {{ active('dashboard.user') }}">🏠 Dashboard</a>
                    @endif
                </li>
            @endif

            {{-- ================= MASTER DATA ================= --}}
            @php
                $showMaster =
                    canAccess('vehicles') ||
                    canAccess('cities') ||
                    canAccess('meeting_points') ||
                    canAccess('schedules') ||
                    canAccess('tariffs') ||
                    canAccess('users');
            @endphp

            @if ($showMaster)
                <small class="text-white-50 mt-3">MASTER DATA</small>

                @if (canAccess('vehicles'))
                    <a href="{{ route('vehicles.index') }}" class="nav-link {{ active('vehicles.*') }}">🚐 Vehicles</a>
                @endif

                @if (canAccess('cities'))
                    <a href="{{ route('cities.index') }}" class="nav-link {{ active('cities.*') }}">🏙 Cities</a>
                @endif

                @if (canAccess('meeting_points'))
                    <a href="{{ route('meeting-points.index') }}" class="nav-link {{ active('meeting-points.*') }}">📍 Meeting Points</a>
                @endif

                @if (canAccess('schedules'))
                    <a href="{{ route('schedules.index') }}" class="nav-link {{ active('schedules.*') }}">🗓 Schedules</a>
                @endif

                @if (canAccess('tariffs'))
                    <a href="{{ route('tariffs.index') }}" class="nav-link {{ active('tariffs.*') }}">💸 Tarif</a>
                @endif

                @if (canAccess('users'))
                    <a href="{{ route('users.index') }}" class="nav-link {{ active('users.*') }}">👤 Users</a>
                @endif
            @endif

            {{-- ================= FINANCE ================= --}}
            @php
                $showFinance =
                    canAccess('finance') ||
                    canAccess('laporan') ||
                    canAccess('setoran') ||
                    canAccess('earnings');
            @endphp

            @if ($showFinance)
                <small class="text-white-50 mt-3">FINANCE</small>

                @if (canAccess('finance'))
                    <a href="{{ route('finance.index') }}" class="nav-link {{ active('finance.index') }}">💰 Dashboard</a>
                @endif

                @if (canAccess('laporan'))
                    <a href="{{ route('finance.report') }}" class="nav-link {{ active('finance.report') }}">📊 Laporan</a>
                @endif

                @if (canAccess('setoran'))
                    <a href="{{ route('finance.setoran') }}" class="nav-link {{ active('finance.setoran') }}">💳 Setoran Driver</a>
                @endif

                @if ($user->isDriver() && canAccess('earnings'))
                    <a href="{{ route('driver.earnings') }}" class="nav-link {{ active('driver.earnings') }}">💰 Earnings</a>
                @endif
            @endif

            {{-- ================= DRIVER ================= --}}
            @if ($user->isDriver() && (canAccess('driver') || canAccess('trip')))
                <small class="text-white-50 mt-3">DRIVER</small>

                @if (canAccess('driver'))
                    <a href="{{ route('driver.index') }}" class="nav-link {{ active('driver.index') }}">📥 Booking Masuk</a>
                @endif

                @if (canAccess('trip'))
                    <a href="{{ route('driver.trips') }}" class="nav-link {{ active('driver.trips') }}">🚗 Trip Saya</a>
                @endif
            @endif

            {{-- ================= BOOKING ================= --}}
            @if ($user->isPassenger() && canAccess('booking'))
                <small class="text-white-50 mt-3">BOOKING</small>

                <a href="{{ route('booking.index') }}" class="nav-link {{ active('booking.index') }}">🎫 Booking</a>
                <a href="{{ route('booking.my') }}" class="nav-link {{ active('booking.my') }}">📋 Booking Saya</a>
            @endif

            {{-- ================= SYSTEM ================= --}}
            <small class="text-white-50 mt-3">SYSTEM</small>

            <a href="{{ route('settings.index') }}" class="nav-link {{ active('settings.*') }}">
                ⚙ Settings
            </a>

            @if (canAccess('activity_logs'))
                <a href="{{ route('activity.index') }}" class="nav-link {{ active('activity.*') }}">
                    📜 Activity Log
                </a>
            @endif

            {{-- ================= CMS ================= --}}
            @if ($user->isSuperAdmin())
                <small class="text-white-50 mt-3">CMS</small>

                <a href="{{ route('admin.sections.index') }}" class="nav-link {{ active('admin.sections.*') }}">
                    🧩 Landing Page
                </a>

                <a href="{{ route('admin.settings.edit') }}" class="nav-link {{ active('admin.settings.*') }}">
                    ⚙️ Web Setting
                </a>
            @endif

            {{-- ================= FEATURE ================= --}}
            @if ($user->isSuperAdmin())
                <small class="text-white-50 mt-3">FEATURE</small>

                <a href="{{ route('features.index') }}" class="nav-link {{ active('features.*') }}">
                    ⚙ Kelola Fitur
                </a>
            @endif

        </ul>
    </div>
</div>

<div id="sidebarOverlay" class="sidebar-overlay"></div>

@endif