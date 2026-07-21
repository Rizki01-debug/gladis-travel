@extends('layouts.app')

@section('content')

<div class="container">
    <h3 class="mb-4">🚗 Detail Booking</h3>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            @php
                $schedule = $booking->schedule;
                $cleanPhone = $booking->phone
                    ? preg_replace('/[^0-9]/', '', $booking->phone)
                    : null;

                // 🔥 Koordinat Pool GLADIS (sesuaikan dengan lokasi sebenarnya)
                $poolLat = -6.200000;  // Ganti dengan latitude pool GLADIS
                $poolLng = 106.800000; // Ganti dengan longitude pool GLADIS

                // 🔥 Parse koordinat pickup
                $pickupLat = null;
                $pickupLng = null;
                $pickupValid = false;
                $pickupDisplay = $booking->pickup_maps ?? '-';

                if (!empty($booking->pickup_maps)) {
                    $coordStr = trim($booking->pickup_maps);

                    // Format 1: -6.690587,108.446212
                    if (strpos($coordStr, ',') !== false) {
                        $parts = explode(',', $coordStr);
                        if (count($parts) === 2) {
                            $pickupLat = floatval(trim($parts[0]));
                            $pickupLng = floatval(trim($parts[1]));
                        }
                    }
                    // Format 2: -6.690587 108.446212
                    elseif (strpos($coordStr, ' ') !== false) {
                        $parts = explode(' ', $coordStr);
                        if (count($parts) === 2) {
                            $pickupLat = floatval(trim($parts[0]));
                            $pickupLng = floatval(trim($parts[1]));
                        }
                    }
                    // Format 3: -6.690587108.446212 (tanpa pemisah)
                    else {
                        preg_match('/([-+]?\d+\.\d+)([-+]?\d+\.\d+)/', $coordStr, $matches);
                        if (count($matches) === 3) {
                            $pickupLat = floatval($matches[1]);
                            $pickupLng = floatval($matches[2]);
                        }
                    }

                    // Validasi koordinat
                    if ($pickupLat !== null && $pickupLng !== null &&
                        $pickupLat >= -90 && $pickupLat <= 90 &&
                        $pickupLng >= -180 && $pickupLng <= 180) {
                        $pickupValid = true;
                        $pickupDisplay = number_format($pickupLat, 6) . ', ' . number_format($pickupLng, 6);
                    }
                }
            @endphp

            <div class="row g-3">

                {{-- ================= PENUMPANG ================= --}}
                <div class="col-md-6">
                    <strong>👤 Penumpang</strong>
                    <div>{{ optional($booking->user)->name ?? '-' }}</div>
                </div>

                {{-- ================= WHATSAPP ================= --}}
                <div class="col-md-6">
                    <strong>📱 WhatsApp</strong>

                    @if ($booking->phone)
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">

                            <a href="https://wa.me/{{ $cleanPhone }}"
                               target="_blank"
                               class="btn btn-success btn-sm">
                                💬 Hubungi
                            </a>

                            <span class="badge bg-light text-dark border">
                                {{ $booking->phone }}
                            </span>

                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    onclick="copyText('{{ $booking->phone }}', this)">
                                📋 Copy
                            </button>

                        </div>
                    @else
                        <div class="text-muted">-</div>
                    @endif
                </div>

                {{-- ================= RUTE ================= --}}
                <div class="col-md-6">
                    <strong>🛣️ Rute</strong>
                    <div>
                        <b>{{ optional($schedule->origin)->name ?? '-' }}</b>
                        →
                        <b>{{ optional($schedule->destination)->name ?? '-' }}</b>
                    </div>
                </div>

                {{-- ================= TANGGAL ================= --}}
                <div class="col-md-6">
                    <strong>📅 Tanggal</strong>
                    <div>{{ $booking->formatted_date ?? '-' }}</div>
                </div>

                {{-- ================= KENDARAAN ================= --}}
                <div class="col-md-6">
                    <strong>🚐 Kendaraan</strong>
                    <div>{{ optional($schedule->vehicle)->name ?? '-' }}</div>
                </div>

                {{-- ================= KURSI ================= --}}
                <div class="col-md-6">
                    <strong>💺 Kursi</strong>
                    <div>
                        @forelse ($booking->seats as $seat)
                            <span class="badge bg-primary">
                                {{ $seat->seat_number }}
                            </span>
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </div>
                </div>

                {{-- ================= PICKUP ================= --}}
                <div class="col-md-6">
                    <strong>📌 Pickup</strong>
                    <div>
                        @if ($booking->pickup_type === 'meeting_point')
                            📍 {{ optional($booking->meetingPoint)->name ?? '-' }}
                        @else
                            🗺️ Dijemput
                            <br>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                <small class="text-muted">
                                    {{ $pickupDisplay }}
                                </small>
                                @if($pickupValid)
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            onclick="copyText('{{ $pickupDisplay }}', this)">
                                        📋 Copy
                                    </button>
                                    <a href="https://www.google.com/maps?q={{ $pickupLat }},{{ $pickupLng }}"
                                       target="_blank"
                                       class="btn btn-primary btn-sm">
                                        🗺️ Buka Maps
                                    </a>
                                @endif
                            </div>
                            @if(!$pickupValid && !empty($booking->pickup_maps))
                                <br>
                                <small class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Format koordinat tidak valid
                                </small>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- ================= HARGA ================= --}}
                <div class="col-md-6">
                    <strong>💰 Estimasi Harga</strong>
                    <div class="text-success fw-semibold">
                        Rp {{ number_format($booking->price_estimation ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="col-12">
                    <strong>Status</strong>
                    <div class="mt-1">
                        @switch($booking->status)
                            @case('pending')
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            @break

                            @case('confirmed')
                                <span class="badge bg-info">Dikonfirmasi</span>
                            @break

                            @case('completed')
                                <span class="badge bg-success">Selesai</span>
                            @break

                            @default
                                <span class="badge bg-secondary">{{ $booking->status }}</span>
                        @endswitch
                    </div>
                </div>

                {{-- ================= MAP PICKUP - DENGAN ROUTE ================= --}}
                @if($booking->pickup_type === 'pickup' && $pickupValid)
                    <div class="col-12 mt-3">
                        <h5>🗺️ Rute Penjemputan</h5>
                        <small class="text-muted d-block mb-2">
                            Dari Pool GLADIS menuju lokasi penjemputan penumpang
                        </small>

                        <div id="pickupMap"
                             style="height: 400px; width: 100%; border-radius: 12px;"
                             class="border">
                        </div>

                        {{-- Informasi jarak dan harga --}}
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <b>📍 Titik Awal:</b>
                                    <span class="text-muted d-block">Pool GLADIS</span>
                                    <small class="text-muted">
                                        {{ number_format($poolLat, 6) }}, {{ number_format($poolLng, 6) }}
                                    </small>
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm mt-1"
                                            onclick="copyText('{{ number_format($poolLat, 6) }}, {{ number_format($poolLng, 6) }}', this)">
                                        📋 Copy
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <b>📍 Titik Akhir:</b>
                                    <span class="text-muted d-block">Lokasi Penjemputan</span>
                                    <small class="text-muted">
                                        {{ $pickupDisplay }}
                                    </small>
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm mt-1"
                                            onclick="copyText('{{ $pickupDisplay }}', this)">
                                        📋 Copy
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <b>🚗 Jarak:</b>
                                    <span id="distance_text" class="text-primary fw-bold">-</span>
                                    <span class="text-muted">KM</span>
                                    <br>
                                    <b>⏱️ Estimasi Waktu:</b>
                                    <span id="time_text" class="text-primary fw-bold">-</span>
                                    <span class="text-muted">menit</span>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="pickup_maps" id="pickup_maps" value="{{ $booking->pickup_maps }}">
                    </div>
                @elseif($booking->pickup_type === 'pickup' && !empty($booking->pickup_maps))
                    <div class="col-12 mt-3">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Koordinat tidak valid:</strong>
                            {{ $booking->pickup_maps }}
                            <br>
                            <small>Pastikan format koordinat menggunakan koma atau spasi (contoh: -6.690587, 108.446212)</small>
                        </div>
                    </div>
                @endif

            </div>

            {{-- ================= AKSI ================= --}}
            @if ($booking->status === 'pending')
                <div class="d-flex flex-wrap gap-2 mt-4">

                    <form action="{{ route('driver.confirm', $booking->id) }}"
                          method="POST"
                          onsubmit="return confirm('Terima booking ini?')">
                        @csrf
                        <button class="btn btn-success">
                            ✅ Terima
                        </button>
                    </form>

                </div>
            @else
                <div class="alert alert-info mt-4">
                    Booking sudah diproses.
                </div>
            @endif

        </div>
    </div>

</div>

@endsection

{{-- ================= CSS ================= --}}
@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<link rel="stylesheet"
      href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css"
      integrity="sha256-khM0Zb6Hj4xH7Q0sj36DVbmmqQlXHdEw9A/CAgT19Ko="
      crossorigin=""/>

<style>
    #pickupMap {
        background: #f0f0f0;
        min-height: 400px;
    }

    .leaflet-routing-container {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 8px;
        padding: 10px;
        margin: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-height: 200px;
        overflow-y: auto;
    }

    .leaflet-routing-alt {
        max-height: 150px !important;
        overflow-y: auto !important;
    }

    .leaflet-routing-alt table {
        font-size: 12px;
    }

    .leaflet-popup-content {
        font-size: 14px;
        font-weight: 500;
    }

    .custom-div-icon .marker-pool {
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }

    .custom-div-icon .marker-pickup {
        background: #28a745;
        color: white;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }

    .btn-copy-success {
        background: #28a745;
        color: white;
        border: none;
    }

    @media (max-width: 768px) {
        #pickupMap {
            height: 300px !important;
        }

        .leaflet-routing-container {
            max-height: 150px;
        }
    }
</style>
@endpush

{{-- ================= SCRIPTS ================= --}}
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"
        integrity="sha256-LT4NF4eqeCg/SAWeTBT6Tf3QxKtwSdQq4kRLJ+DHrc8="
        crossorigin=""></script>

<script>
// ================= FUNCTION COPY =================
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalText = btn.innerHTML;
        btn.innerHTML = '✅ Copied';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    }).catch(() => {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);

        const originalText = btn.innerHTML;
        btn.innerHTML = '✅ Copied';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    });
}

// ================= INISIALISASI MAP =================
@if($booking->pickup_type === 'pickup' && $pickupValid)

document.addEventListener('DOMContentLoaded', function() {
    const pickupLat = {{ $pickupLat }};
    const pickupLng = {{ $pickupLng }};
    const poolLat = {{ $poolLat }};
    const poolLng = {{ $poolLng }};

    console.log('🗺️ Initializing map...');
    console.log('📍 Pool:', poolLat, poolLng);
    console.log('📍 Pickup:', pickupLat, pickupLng);

    // 🔥 Inisialisasi Map
    const map = L.map('pickupMap').setView([poolLat, poolLng], 13);

    // 🔥 Tile Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    // 🔥 Custom Icon untuk Pool
    const poolIcon = L.divIcon({
        className: 'custom-div-icon',
        html: '<div class="marker-pool">🏠</div>',
        iconSize: [36, 36],
        iconAnchor: [18, 18],
        popupAnchor: [0, -18]
    });

    // 🔥 Custom Icon untuk Pickup
    const pickupIcon = L.divIcon({
        className: 'custom-div-icon',
        html: '<div class="marker-pickup">📍</div>',
        iconSize: [36, 36],
        iconAnchor: [18, 18],
        popupAnchor: [0, -18]
    });

    // 🔥 Marker Pool
    L.marker([poolLat, poolLng], { icon: poolIcon })
        .addTo(map)
        .bindPopup('<b>🏠 Pool GLADIS</b><br>Titik Keberangkatan');

    // 🔥 Marker Pickup
    L.marker([pickupLat, pickupLng], { icon: pickupIcon })
        .addTo(map)
        .bindPopup('<b>📍 Lokasi Penjemputan</b><br>{{ $pickupDisplay }}')
        .openPopup();

    // 🔥 ROUTING MACHINE
    const routingControl = L.Routing.control({
        waypoints: [
            L.latLng(poolLat, poolLng),
            L.latLng(pickupLat, pickupLng)
        ],
        routeWhileDragging: false,
        draggableWaypoints: false,
        addWaypoints: false,
        fitSelectedRoutes: true,
        showAlternatives: false,
        createMarker: function(i, wp) {
            return null;
        },
        lineOptions: {
            styles: [
                {
                    color: '#0d6efd',
                    opacity: 0.8,
                    weight: 4,
                    dashArray: '10, 8'
                }
            ],
            extendToWaypoints: true,
            missingRouteTolerance: 0
        },
        router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1',
            profile: 'driving'
        }),
        formatter: new L.Routing.Formatter({
            units: 'metric',
            roundingSensitivity: 1,
            distanceTemplate: '{distance} km',
            timeTemplate: '{time} menit'
        })
    }).addTo(map);

    // 🔥 Event listener untuk menangkap jarak
    routingControl.on('routesfound', function(e) {
        const routes = e.routes;
        if (routes.length > 0) {
            const route = routes[0];
            const distance = (route.summary.totalDistance / 1000).toFixed(1);
            const time = Math.round(route.summary.totalTime / 60);

            document.getElementById('distance_text').textContent = distance;
            document.getElementById('time_text').textContent = time;

            console.log('🚗 Jarak:', distance, 'KM');
            console.log('⏱️ Waktu:', time, 'menit');
        }
    });

    // 🔥 Fit bounds ke kedua marker
    setTimeout(() => {
        const bounds = L.latLngBounds([
            [poolLat, poolLng],
            [pickupLat, pickupLng]
        ]);
        map.fitBounds(bounds, { padding: [50, 50] });
    }, 500);

    // 🔥 Resize map
    setTimeout(() => {
        map.invalidateSize();
    }, 1000);

    // 🔥 Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            map.invalidateSize();
        }, 250);
    });

    // 🔥 Handle tab visibility
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(function() {
                map.invalidateSize();
            }, 500);
        }
    });
});

@endif
</script>
@endpush