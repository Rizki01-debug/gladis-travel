@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
@endphp

@if ($user)
    <div class="sidebar p-3">

        <h4 class="mb-4">GLADIS</h4>

        <ul class="nav flex-column">

            {{-- ================= DASHBOARD ================= --}}
            <li class="nav-item mb-2">
                @if ($user->isSuperAdmin())
                    <a href="{{ route('superadmin.dashboard') }}"
                        class="nav-link text-white {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        🏠 Dashboard
                    </a>
                @elseif ($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        🏠 Dashboard
                    </a>
                @elseif ($user->isDriver())
                    <a href="{{ route('driver.index') }}"
                        class="nav-link text-white {{ request()->routeIs('driver.*') ? 'active' : '' }}">
                        🏠 Dashboard
                    </a>
                @elseif ($user->isPassenger())
                    <a href="{{ route('booking.index') }}"
                        class="nav-link text-white {{ request()->routeIs('booking.*') ? 'active' : '' }}">
                        🏠 Dashboard
                    </a>
                @endif
            </li>

            {{-- ================= SUPER ADMIN ================= --}}
            @if ($user->isSuperAdmin())
                <small class="text-white-50 mt-2">MASTER DATA</small>

                <li class="nav-item">
                    <a href="{{ route('vehicles.index') }}"
                        class="nav-link text-white {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                        🚐 Vehicles
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('cities.index') }}"
                        class="nav-link text-white {{ request()->routeIs('cities.*') ? 'active' : '' }}">
                        🏙 Cities
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('meeting-points.index') }}"
                        class="nav-link text-white {{ request()->routeIs('meeting-points.*') ? 'active' : '' }}">
                        📍 Meeting Points
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('schedules.index') }}"
                        class="nav-link text-white {{ request()->routeIs('schedules.*') ? 'active' : '' }}">
                        🗓 Schedules
                    </a>
                </li>
            @endif

            {{-- ================= FINANCE ================= --}}
            @if ($user->isAdmin() || $user->isSuperAdmin())
                <small class="text-white-50 mt-3">FINANCE</small>

                <li class="nav-item">
                    <a href="{{ route('finance.index') }}"
                        class="nav-link text-white {{ request()->routeIs('finance.index') ? 'active' : '' }}">
                        💰 Finance
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('finance.report') }}"
                        class="nav-link text-white {{ request()->routeIs('finance.report') ? 'active' : '' }}">
                        📊 Laporan
                    </a>
                </li>
            @endif

            {{-- ================= DRIVER ================= --}}
            @if ($user->isDriver())
                <small class="text-white-50 mt-3">DRIVER</small>

                <li class="nav-item">
                    <a href="{{ route('driver.index') }}"
                        class="nav-link text-white {{ request()->routeIs('driver.index') ? 'active' : '' }}">
                        📥 Booking Masuk
                    </a>
                </li>

                {{-- 🔥 TAMBAHAN --}}
                <li class="nav-item">
                    <a href="{{ route('driver.trip.index') }}"
                        class="nav-link text-white {{ request()->routeIs('driver.trip.*') ? 'active' : '' }}">
                        🚗 Trip Saya
                    </a>
                </li>
            @endif

            {{-- ================= PASSENGER ================= --}}
            @if ($user->isPassenger())
                <small class="text-white-50 mt-3">BOOKING</small>

                <li class="nav-item">
                    <a href="{{ route('booking.index') }}"
                        class="nav-link text-white {{ request()->routeIs('booking.*') ? 'active' : '' }}">
                        🎫 Booking
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('booking.my') }}" class="nav-link text-white">
                        📋 Booking Saya
                    </a>
                </li>
                
            @endif

            {{-- ================= SETTINGS ================= --}}
            <li class="nav-item mt-4">
                <a href="{{ route('settings.index') }}"
                    class="nav-link text-white {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    ⚙ Settings
                </a>
            </li>

        </ul>
    </div>
@endif
