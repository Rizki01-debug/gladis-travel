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
                        <a href="{{ route('superadmin.dashboard') }}" class="nav-link {{ active('superadmin.dashboard') }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    @elseif ($user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ active('admin.dashboard') }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    @elseif ($user->isDriver())
                        <a href="{{ route('driver.dashboard') }}" class="nav-link {{ active('driver.dashboard') }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    @elseif ($user->isPassenger())
                        <a href="{{ route('dashboard.user') }}" class="nav-link {{ active('dashboard.user') }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
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
                    <a href="{{ route('vehicles.index') }}" class="nav-link {{ active('vehicles.*') }}">
                        <i class="bi bi-truck me-2"></i> Vehicles
                    </a>
                @endif

                @if (canAccess('cities'))
                    <a href="{{ route('cities.index') }}" class="nav-link {{ active('cities.*') }}">
                        <i class="bi bi-building me-2"></i> Cities
                    </a>
                @endif

                @if (canAccess('meeting_points'))
                    <a href="{{ route('meeting-points.index') }}" class="nav-link {{ active('meeting-points.*') }}">
                        <i class="bi bi-geo-alt me-2"></i> Meeting Points
                    </a>
                @endif

                @if (canAccess('schedules'))
                    <a href="{{ route('schedules.index') }}" class="nav-link {{ active('schedules.*') }}">
                        <i class="bi bi-calendar-event me-2"></i> Schedules
                    </a>
                @endif

                @if (canAccess('tariffs'))
                    <a href="{{ route('tariffs.index') }}" class="nav-link {{ active('tariffs.*') }}">
                        <i class="bi bi-cash-coin me-2"></i> Tarif
                    </a>
                @endif

                @if (canAccess('users'))
                    <a href="{{ route('users.index') }}" class="nav-link {{ active('users.*') }}">
                        <i class="bi bi-people me-2"></i> Users
                    </a>
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
                    <a href="{{ route('finance.index') }}" class="nav-link {{ active('finance.index') }}">
                        <i class="bi bi-wallet2 me-2"></i> Dashboard
                    </a>
                @endif

                @if (canAccess('laporan'))
                    <a href="{{ route('finance.report') }}" class="nav-link {{ active('finance.report') }}">
                        <i class="bi bi-bar-chart me-2"></i> Laporan
                    </a>
                @endif

                {{-- @if (canAccess('setoran'))
                    <a href="{{ route('finance.setoran') }}" class="nav-link {{ active('finance.setoran') }}">
                        <i class="bi bi-credit-card me-2"></i> Setoran Driver
                    </a>
                @endif --}}

                @if ($user->isDriver() && canAccess('earnings'))
                    <a href="{{ route('driver.earnings') }}" class="nav-link {{ active('driver.earnings') }}">
                        <i class="bi bi-cash-stack me-2"></i> Earnings
                    </a>
                @endif
            @endif


            {{-- ================= DRIVER ================= --}}
            @if ($user->isDriver() && (canAccess('driver') || canAccess('trip')))
                <small class="text-white-50 mt-3">DRIVER</small>

                @if (canAccess('driver'))
                    <a href="{{ route('driver.index') }}" class="nav-link {{ active('driver.index') }}">
                        <i class="bi bi-inbox me-2"></i> Booking Masuk
                    </a>
                @endif

                @if (canAccess('trip'))
                    <a href="{{ route('driver.trips') }}" class="nav-link {{ active('driver.trips') }}">
                        <i class="bi bi-car-front me-2"></i> Trip Saya
                    </a>
                @endif
            @endif


            {{-- ================= BOOKING ================= --}}
            @if ($user->isPassenger() && canAccess('booking'))
                <small class="text-white-50 mt-3">BOOKING</small>

                <a href="{{ route('booking.index') }}" class="nav-link {{ active('booking.index') }}">
                    <i class="bi bi-ticket-perforated me-2"></i> Booking
                </a>

                <a href="{{ route('booking.my') }}" class="nav-link {{ active('booking.my') }}">
                    <i class="bi bi-clipboard-check me-2"></i> Booking Saya
                </a>

                {{-- 🔥 PROFILE (TAMBAHAN) --}}
                <a href="{{ route('passenger.profile.index') }}" class="nav-link {{ active('passenger.profile.index') }}">
                    <i class="bi bi-person-circle me-2"></i> Profile
                </a>
            @endif


            {{-- ================= SYSTEM ================= --}}
            <small class="text-white-50 mt-3">SYSTEM</small>

            <a href="{{ route('settings.index') }}" class="nav-link {{ active('settings.*') }}">
                <i class="bi bi-gear me-2"></i> Settings
            </a>

            @if (canAccess('activity_logs'))
                <a href="{{ route('activity.index') }}" class="nav-link {{ active('activity.*') }}">
                    <i class="bi bi-clock-history me-2"></i> Activity Log
                </a>
            @endif


            {{-- ================= CMS ================= --}}
            @if ($user->isSuperAdmin())
                <small class="text-white-50 mt-3">CMS</small>

                <a href="{{ route('admin.sections.index') }}" class="nav-link {{ active('admin.sections.*') }}">
                    <i class="bi bi-grid-1x2 me-2"></i> Landing Page
                </a>

                <a href="{{ route('admin.settings.edit') }}" class="nav-link {{ active('admin.settings.*') }}">
                    <i class="bi bi-sliders me-2"></i> Web Setting
                </a>
            @endif


            {{-- ================= FEATURE ================= --}}
            @if ($user->isSuperAdmin())
                <small class="text-white-50 mt-3">FEATURE</small>

                <a href="{{ route('features.index') }}" class="nav-link {{ active('features.*') }}">
                    <i class="bi bi-toggles me-2"></i> Kelola Fitur
                </a>
            @endif

        </ul>
    </div>
</div>

<div id="sidebarOverlay" class="sidebar-overlay"></div>

@endif