@php
    use Illuminate\Support\Facades\Auth;

    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    // 🔥 fallback theme
    $theme = $theme ?? '#1e293b';

    // 🔥 SAFE helper (kalau helper error, sidebar ga crash)
    if (!function_exists('featureActive')) {
        function featureActive($name)
        {
            return true;
        }
    }
@endphp

@if ($user)
    <div class="sidebar p-3 text-white" style="background: {{ $theme }}; min-height:100vh;">

        <h4 class="mb-4">🚐 GLADIS</h4>

        <ul class="nav flex-column">

            {{-- ================= DASHBOARD ================= --}}
            <li class="nav-item mb-3">
                @if ($user->isSuperAdmin())
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-link text-white">🏠 Dashboard</a>
                @elseif ($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="nav-link text-white">🏠 Dashboard</a>
                @elseif ($user->isDriver())
                    <a href="{{ route('driver.dashboard') }}" class="nav-link text-white">🏠 Dashboard</a>
                @elseif ($user->isPassenger())
                    <a href="{{ route('booking.index') }}" class="nav-link text-white">🏠 Dashboard</a>
                @endif
            </li>

            {{-- ================= SUPER ADMIN ================= --}}
            @if ($user->isSuperAdmin())

                <small class="text-white-50">MASTER DATA</small>

                @if (featureActive('vehicles'))
                    <li class="nav-item"><a href="{{ route('vehicles.index') }}" class="nav-link text-white">🚐
                            Vehicles</a></li>
                @endif

                @if (featureActive('cities'))
                    <li class="nav-item"><a href="{{ route('cities.index') }}" class="nav-link text-white">🏙 Cities</a>
                    </li>
                @endif

                @if (featureActive('meeting_points'))
                    <li class="nav-item"><a href="{{ route('meeting-points.index') }}" class="nav-link text-white">📍
                            Meeting Points</a></li>
                @endif

                @if (featureActive('schedules'))
                    <li class="nav-item"><a href="{{ route('schedules.index') }}" class="nav-link text-white">🗓
                            Schedules</a></li>
                @endif

                @if (featureActive('tariffs'))
                    <li class="nav-item"><a href="{{ route('tariffs.index') }}" class="nav-link text-white">💸 Tarif</a>
                    </li>
                @endif

                {{-- 🔥 STEP 2 FEATURE CONTROL --}}
                <small class="text-white-50 mt-3">ACTIVITY LOG</small>

                @if (featureActive('activity_logs'))
                    <li class="nav-item">
                        <a href="{{ route('activity.index') }}"
                            class="nav-link text-white {{ request()->routeIs('activity.*') ? 'active fw-bold' : '' }}">
                            📜 Activity Log
                        </a>
                    </li>
                @endif

                {{-- 🔥 STEP 2 FEATURE CONTROL --}}
                <small class="text-white-50 mt-3">SYSTEM</small>

                <li class="nav-item">
                    <a href="{{ route('features.index') }}"
                        class="nav-link text-white {{ request()->routeIs('features.*') ? 'active fw-bold' : '' }}">
                        ⚙ Kelola Fitur
                    </a>
                </li>

            @endif

            {{-- ================= ADMIN ================= --}}
            @if ($user->isAdmin())

                <small class="text-white-50 mt-3">MASTER DATA</small>

                @if (featureActive('vehicles'))
                    <li class="nav-item"><a href="{{ route('vehicles.index') }}" class="nav-link text-white">🚐
                            Kendaraan</a></li>
                @endif

                @if (featureActive('meeting_points'))
                    <li class="nav-item"><a href="{{ route('meeting-points.index') }}" class="nav-link text-white">📍
                            Meeting Point</a></li>
                @endif

                @if (featureActive('schedules'))
                    <li class="nav-item"><a href="{{ route('schedules.index') }}" class="nav-link text-white">🗓
                            Jadwal</a></li>
                @endif

            @endif

            {{-- ================= FINANCE ================= --}}
            @if ($user->isAdmin() || $user->isSuperAdmin())

                @if (featureActive('finance'))
                    <small class="text-white-50 mt-3">FINANCE</small>

                    <li class="nav-item">
                        <a href="{{ route('finance.index') }}" class="nav-link text-white">💰 Dashboard Keuangan</a>
                    </li>
                @endif

                @if (featureActive('laporan'))
                    <li class="nav-item">
                        <a href="{{ route('finance.report') }}" class="nav-link text-white">📊 Laporan</a>
                    </li>
                @endif

                @if (featureActive('finance'))
                    <li class="nav-item">
                        <a href="{{ route('finance.setoran') }}" class="nav-link text-white">💳 Setoran Driver</a>
                    </li>
                @endif

            @endif

            {{-- ================= DRIVER ================= --}}
            @if ($user->isDriver())

                <small class="text-white-50 mt-3">DRIVER</small>

                {{-- BOOKING MASUK --}}
                @if (featureActive('driver'))
                    <li class="nav-item">
                        <a href="{{ route('driver.index') }}"
                            class="nav-link text-white {{ request()->routeIs('driver.index') ? 'active bg-light text-dark' : '' }}">
                            📥 Booking Masuk
                        </a>
                    </li>
                @endif

                {{-- TRIP --}}
                @if (featureActive('trip'))
                    <li class="nav-item">
                        <a href="{{ route('driver.trips') }}"
                            class="nav-link text-white {{ request()->routeIs('driver.trips') ? 'active bg-light text-dark' : '' }}">
                            🚗 Trip Saya
                        </a>
                    </li>
                @endif

                {{-- EARNINGS 🔥 --}}
                <li class="nav-item">
                    <a href="{{ route('driver.earnings') }}"
                        class="nav-link text-white {{ request()->routeIs('driver.earnings') ? 'active bg-light text-dark' : '' }}">
                        💰 Earnings
                    </a>
                </li>

            @endif

            {{-- ================= PASSENGER ================= --}}
            @if ($user->isPassenger())

                <small class="text-white-50 mt-3">BOOKING</small>

                @if (featureActive('booking'))
                    <li class="nav-item">
                        <a href="{{ route('booking.index') }}" class="nav-link text-white">🎫 Booking</a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('booking.my') }}" class="nav-link text-white">📋 Booking Saya</a>
                    </li>
                @endif

            @endif

            {{-- ================= SETTINGS ================= --}}
            <li class="nav-item mt-4">
                <a href="{{ route('settings.index') }}" class="nav-link text-white">⚙ Settings</a>
            </li>

        </ul>
    </div>
@endif
