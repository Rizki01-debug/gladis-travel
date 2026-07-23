@extends('layouts.app')

@section('content')

<div class="container">
    <h3 class="mb-4">🚗 Detail Booking</h3>

    <div class="row">
        {{-- ================= KOLOM KIRI: INFORMASI ================= --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    @php
                        $schedule = $booking->schedule;
                        $cleanPhone = $booking->phone
                            ? preg_replace('/[^0-9]/', '', $booking->phone)
                            : null;

                        // 🔥 Koordinat Pool GLADIS
                        $poolLat = -6.32639;
                        $poolLng = 108.32;

                        // 🔥 Parse koordinat pickup
                        $pickupLat = null;
                        $pickupLng = null;
                        $pickupValid = false;
                        $pickupDisplay = $booking->pickup_maps ?? '-';

                        if (!empty($booking->pickup_maps)) {
                            $coordStr = trim($booking->pickup_maps);

                            if (strpos($coordStr, ',') !== false) {
                                $parts = explode(',', $coordStr);
                                if (count($parts) === 2) {
                                    $pickupLat = floatval(trim($parts[0]));
                                    $pickupLng = floatval(trim($parts[1]));
                                }
                            } elseif (strpos($coordStr, ' ') !== false) {
                                $parts = explode(' ', $coordStr);
                                if (count($parts) === 2) {
                                    $pickupLat = floatval(trim($parts[0]));
                                    $pickupLng = floatval(trim($parts[1]));
                                }
                            } else {
                                preg_match('/([-+]?\d+\.\d+)([-+]?\d+\.\d+)/', $coordStr, $matches);
                                if (count($matches) === 3) {
                                    $pickupLat = floatval($matches[1]);
                                    $pickupLng = floatval($matches[2]);
                                }
                            }

                            if ($pickupLat !== null && $pickupLng !== null &&
                                $pickupLat >= -90 && $pickupLat <= 90 &&
                                $pickupLng >= -180 && $pickupLng <= 180) {
                                $pickupValid = true;
                                $pickupDisplay = number_format($pickupLat, 6) . ', ' . number_format($pickupLng, 6);
                            }
                        }
                    @endphp

                    {{-- PENUMPANG --}}
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="text-muted small">👤 Penumpang</label>
                        <div class="fw-semibold">{{ optional($booking->user)->name ?? '-' }}</div>
                    </div>

                    {{-- WHATSAPP --}}
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="text-muted small">📱 WhatsApp</label>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            @if ($booking->phone)
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn btn-success btn-sm">
                                    💬 Hubungi
                                </a>
                                <span class="badge bg-light text-dark border">{{ $booking->phone }}</span>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyText('{{ $booking->phone }}', this)">
                                    📋 Copy
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    {{-- RUTE --}}
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="text-muted small">🛣️ Rute</label>
                        <div class="fw-semibold">
                            {{ optional($schedule->origin)->name ?? '-' }}
                            <i class="fas fa-arrow-right mx-1 text-muted"></i>
                            {{ optional($schedule->destination)->name ?? '-' }}
                        </div>
                    </div>

                    {{-- TANGGAL --}}
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="text-muted small">📅 Tanggal</label>
                        <div class="fw-semibold">{{ $booking->formatted_date ?? '-' }}</div>
                    </div>

                    {{-- KENDARAAN --}}
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="text-muted small">🚐 Kendaraan</label>
                        <div class="fw-semibold">{{ optional($schedule->vehicle)->name ?? '-' }}</div>
                    </div>

                    {{-- KURSI --}}
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="text-muted small">💺 Kursi</label>
                        <div>
                            @forelse ($booking->seats as $seat)
                                <span class="badge bg-primary">{{ $seat->seat_number }}</span>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </div>
                    </div>

                    {{-- PICKUP --}}
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="text-muted small">📌 Pickup</label>
                        <div>
                            @if ($booking->pickup_type === 'meeting_point')
                                📍 {{ optional($booking->meetingPoint)->name ?? '-' }}
                                <br>
                                <small class="text-muted">Meeting Point</small>
                            @else
                                🗺️ Dijemput
                                <br>
                                <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                    <code class="bg-light p-1 rounded small">{{ $pickupDisplay }}</code>
                                    @if($pickupValid)
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyText('{{ $pickupDisplay }}', this)">
                                            📋 Copy
                                        </button>
                                        <a href="https://www.google.com/maps?q={{ $pickupLat }},{{ $pickupLng }}" target="_blank" class="btn btn-primary btn-sm">
                                            🗺️ Maps
                                        </a>
                                    @endif
                                </div>
                                @if(!$pickupValid && !empty($booking->pickup_maps))
                                    <small class="text-danger">Format koordinat tidak valid</small>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- HARGA --}}
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="text-muted small">💰 Estimasi Harga</label>
                        <div class="text-success fw-bold fs-5">
                            Rp {{ number_format($booking->price_estimation ?? 0, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <div>
                            @switch($booking->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark">⏳ Menunggu</span>
                                    @break
                                @case('confirmed')
                                    <span class="badge bg-info">✅ Dikonfirmasi</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-success">🎉 Selesai</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $booking->status }}</span>
                            @endswitch
                        </div>
                    </div>

                    {{-- AKSI --}}
                    @if ($booking->status === 'pending')
                        <div class="mt-3">
                            <form action="{{ route('driver.confirm', $booking->id) }}" method="POST" onsubmit="return confirm('Terima booking ini?')">
                                @csrf
                                <button class="btn btn-success w-100">
                                    ✅ Terima Booking
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle"></i> Booking sudah diproses.
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- ================= KOLOM KANAN: MAP ================= --}}
        <div class="col-lg-7 mt-3 mt-lg-0">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">

                    @if($pickupValid)
                        {{-- HEADER MAP --}}
                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">🗺️ Rute Penjemputan</h5>
                                <small class="text-muted">
                                    @if($booking->pickup_type === 'meeting_point')
                                        📍 Meeting Point: {{ optional($booking->meetingPoint)->name ?? '-' }}
                                    @else
                                        🗺️ Dari Pool GLADIS → Lokasi Penjemputan
                                    @endif
                                </small>
                            </div>
                        </div>

                        {{-- MAP --}}
                        <div id="pickupMap" style="height: 500px; width: 100%;"></div>

                        {{-- FOOTER MAP --}}
                        <div class="p-3 bg-light border-top">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="p-2 bg-white rounded shadow-sm text-center">
                                        <div class="text-danger">🏠</div>
                                        <small class="text-muted d-block">Pool GLADIS</small>
                                        <small class="text-muted" style="font-size: 10px;">
                                            {{ number_format($poolLat, 6) }}, {{ number_format($poolLng, 6) }}
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-2 bg-white rounded shadow-sm text-center">
                                        <div class="text-success">📍</div>
                                        <small class="text-muted d-block">
                                            @if($booking->pickup_type === 'meeting_point')
                                                Meeting Point
                                            @else
                                                Penjemputan
                                            @endif
                                        </small>
                                        <small class="text-muted" style="font-size: 10px;">
                                            {{ $pickupDisplay }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="pickup_maps" id="pickup_maps" value="{{ $booking->pickup_maps }}">
                    @elseif(!empty($booking->pickup_maps))
                        <div class="p-5 text-center">
                            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                            <h5>Koordinat tidak valid</h5>
                            <p class="text-muted">{{ $booking->pickup_maps }}</p>
                            <small class="text-muted">Pastikan format koordinat: -6.690587, 108.446212</small>
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <i class="fas fa-map-marked-alt text-muted fa-3x mb-3"></i>
                            <h5>Lokasi tidak tersedia</h5>
                            <p class="text-muted">
                                @if($booking->pickup_type === 'meeting_point')
                                    Penumpang memilih pickup di meeting point: 
                                    <strong>{{ optional($booking->meetingPoint)->name ?? '-' }}</strong>
                                @else
                                    Penumpang belum menentukan lokasi pickup
                                @endif
                            </p>
                        </div>
                    @endif

                </div>
            </div>
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
        background: #e8ecf1;
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
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        border: 3px solid white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .custom-div-icon .marker-pickup {
        background: #28a745;
        color: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        border: 3px solid white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    /* Animasi garis mengikuti jalan */
    .leaflet-routing-line {
        animation: dash 1s linear infinite;
    }

    @keyframes dash {
        to {
            stroke-dashoffset: -20;
        }
    }

    @media (max-width: 768px) {
        #pickupMap {
            height: 350px !important;
        }

        .leaflet-routing-container {
            max-height: 120px;
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

// ================= UPDATE UI =================
function updateUI(distance, time) {
    const distanceText = distance + ' KM';
    const timeText = time + ' menit';

    const elements = {
        distance_badge: document.getElementById('distance_badge'),
        time_badge: document.getElementById('time_badge'),
        distance_footer: document.getElementById('distance_footer'),
        time_footer: document.getElementById('time_footer')
    };

    if (elements.distance_badge) elements.distance_badge.textContent = distanceText;
    if (elements.time_badge) elements.time_badge.textContent = timeText;
    if (elements.distance_footer) elements.distance_footer.textContent = distanceText;
    if (elements.time_footer) elements.time_footer.textContent = timeText;
}

// ================= INISIALISASI MAP =================
@if($pickupValid)

document.addEventListener('DOMContentLoaded', function() {
    const pickupLat = {{ $pickupLat }};
    const pickupLng = {{ $pickupLng }};
    const poolLat = {{ $poolLat }};
    const poolLng = {{ $poolLng }};

    console.log('🗺️ Initializing map...');
    console.log('📍 Pool:', poolLat, poolLng);
    console.log('📍 Pickup:', pickupLat, pickupLng);

    // 🔥 Inisialisasi Map
    const map = L.map('pickupMap', {
        center: [poolLat, poolLng],
        zoom: 13,
        zoomControl: true,
        fadeAnimation: true,
        zoomAnimation: true
    });

    // 🔥 Tile Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    // 🔥 Custom Icon Pool
    const poolIcon = L.divIcon({
        className: 'custom-div-icon',
        html: '<div class="marker-pool">🏠</div>',
        iconSize: [40, 40],
        iconAnchor: [20, 20],
        popupAnchor: [0, -20]
    });

    // 🔥 Custom Icon Pickup
    const pickupIcon = L.divIcon({
        className: 'custom-div-icon',
        html: '<div class="marker-pickup">📍</div>',
        iconSize: [40, 40],
        iconAnchor: [20, 20],
        popupAnchor: [0, -20]
    });

    // 🔥 Marker Pool
    L.marker([poolLat, poolLng], { icon: poolIcon })
        .addTo(map)
        .bindPopup(`
            <b>🏠 Pool GLADIS</b><br>
            Titik Keberangkatan<br>
            <small>${poolLat.toFixed(6)}, ${poolLng.toFixed(6)}</small>
        `)
        .openPopup();

    // 🔥 Marker Pickup
    L.marker([pickupLat, pickupLng], { icon: pickupIcon })
        .addTo(map)
        .bindPopup(`
            <b>📍 Lokasi Penjemputan</b><br>
            ${pickupLat.toFixed(6)}, ${pickupLng.toFixed(6)}
        `);

    // ================= ROUTING MACHINE =================
    // 🔥 SAMA SEPERTI VERSI PENUMPANG
    // Garis akan mengikuti jalan (bukan garis lurus)

    let routingControl = null;

    try {
        routingControl = L.Routing.control({
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
                        opacity: 0.9,
                        weight: 5
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

        // 🔥 Event listener untuk routing
        routingControl.on('routesfound', function(e) {
            const routes = e.routes;
            if (routes.length > 0) {
                const route = routes[0];
                const distance = (route.summary.totalDistance / 1000).toFixed(1);
                const time = Math.round(route.summary.totalTime / 60);

                updateUI(distance, time);
                console.log('✅ Rute mengikuti jalan ditemukan!');
                console.log('   📏 Jarak:', distance, 'KM');
                console.log('   ⏱️ Waktu:', time, 'menit');
                console.log('   🛣️ Jumlah instruksi:', route.instructions.length);
            }
        });

        routingControl.on('routingerror', function(e) {
            console.warn('⚠️ Routing error:', e.error);
            updateUI('?', '?');
        });

        routingControl.on('routingstart', function() {
            console.log('🔄 Menghitung rute tercepat...');
        });

        routingControl.on('routingend', function() {
            console.log('✅ Selesai menghitung rute');
        });

    } catch (error) {
        console.error('❌ Error routing:', error);
        updateUI('?', '?');

        // 🔥 FALLBACK: GARIS LURUS JIKA ROUTING GAGAL
        const latlngs = [
            [poolLat, poolLng],
            [pickupLat, pickupLng]
        ];

        L.polyline(latlngs, {
            color: '#dc3545',
            weight: 4,
            opacity: 0.7,
            dashArray: '10, 8'
        }).addTo(map);
    }

    // 🔥 Fit bounds ke kedua marker
    setTimeout(() => {
        const bounds = L.latLngBounds([
            [poolLat, poolLng],
            [pickupLat, pickupLng]
        ]);
        map.fitBounds(bounds, { padding: [60, 60] });
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

    // 🔥 Handle bootstrap modal/tab
    document.addEventListener('shown.bs.tab', function() {
        setTimeout(function() {
            map.invalidateSize();
        }, 500);
    });
});

@endif
</script>
@endpush