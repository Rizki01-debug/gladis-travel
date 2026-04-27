@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold">🚐 Jadwal Keberangkatan</h4>
                <small class="text-muted">Kelola rute dan jadwal travel</small>
            </div>

            <a href="{{ route('schedules.create') }}" class="btn btn-primary">
                ➕ Tambah Jadwal
            </a>
        </div>

        {{-- TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table align-middle">

                        <thead class="table-light text-center">
                            <tr>
                                <th>Rute</th>
                                <th>Kendaraan</th>
                                <th>Jam</th>
                                <th>Map</th>
                                <th>Jarak</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($schedules as $s)
                                <tr>
                                    <td>
                                        <b>{{ $s->origin->name ?? '-' }}</b><br>
                                        <small>→ {{ $s->destination->name ?? '-' }}</small>
                                    </td>

                                    <td class="text-center">
                                        {{ $s->vehicle->name ?? '-' }}
                                    </td>

                                    <td class="text-center">
                                        {{ $s->formatted_time ?? '-' }}
                                    </td>

                                    <td class="text-center">
                                        <button class="btn btn-info btn-sm" onclick="showRoute({{ $s->id }})">
                                            Lihat
                                        </button>
                                    </td>

                                    <td class="text-center text-primary fw-bold" id="distance-{{ $s->id }}">
                                        -
                                    </td>

                                    {{-- 🔥 CRUD --}}
                                    <td class="text-center">
                                        <a href="{{ route('schedules.edit', $s->id) }}" class="btn btn-warning btn-sm">
                                            ✏️
                                        </a>

                                        <form action="{{ route('schedules.destroy', $s->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus jadwal?')">
                                                🗑️
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        🚫 Belum ada jadwal
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- PAGINATION --}}
                @if ($schedules->hasPages())
                    <div class="mt-3">
                        {{ $schedules->links('pagination::bootstrap-5') }}
                    </div>
                @endif

            </div>
        </div>

        {{-- MAP --}}
        <div class="card mt-4 border-0 shadow-sm">
            <div class="card-body">
                <h6>🗺 Preview Rute</h6>
                <div id="map" style="height:400px;"></div>
            </div>
        </div>

    </div>
@endsection


@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@endpush


@push('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const map = L.map('map').setView([-6.2, 106.8], 8);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            const schedules = @json($schedules->items());

            let markers = [];
            let polyline = null;

            // 🔥 CACHE (ANTI LAG)
            let routeCache = {};

            function clearMap() {
                markers.forEach(m => map.removeLayer(m));
                markers = [];

                if (polyline) {
                    map.removeLayer(polyline);
                    polyline = null;
                }
            }

            function setDistance(id, value) {
                const el = document.getElementById('distance-' + id);
                if (el) el.innerHTML = value;
            }

            function drawRoute(route, id) {

                const latlngs = route.geometry.coordinates.map(c => [c[1], c[0]]);

                polyline = L.polyline(latlngs, {
                    weight: 5
                }).addTo(map);

                map.fitBounds(polyline.getBounds(), {
                    padding: [40, 40]
                });

                // 🔥 MARKER HEMAT (CUMA START & END)
                const start = latlngs[0];
                const end = latlngs[latlngs.length - 1];

                markers.push(L.marker(start).addTo(map).bindPopup("Start"));
                markers.push(L.marker(end).addTo(map).bindPopup("End"));

                const km = (route.distance / 1000).toFixed(1);
                setDistance(id, km + " KM");
            }

            window.showRoute = async function(id) {

                clearMap();

                // 🔥 CACHE CHECK
                if (routeCache[id]) {
                    drawRoute(routeCache[id], id);
                    return;
                }

                const schedule = schedules.find(s => s.id == id);

                if (!schedule || !schedule.route_points) {
                    alert("Rute tidak tersedia");
                    return;
                }

                const sorted = schedule.route_points.sort((a, b) => a.order - b.order);

                let coords = [];

                sorted.forEach(rp => {
                    let p = rp.meeting_point;

                    if (p && p.latitude && p.longitude) {
                        coords.push(`${p.longitude},${p.latitude}`);
                    }
                });

                if (coords.length < 2) {
                    alert("Minimal 2 titik");
                    return;
                }

                try {

                    const url =
                        `https://router.project-osrm.org/route/v1/driving/${coords.join(';')}?overview=simplified&geometries=geojson`;

                    const res = await fetch(url);
                    const data = await res.json();

                    if (!data.routes.length) {
                        alert("Rute tidak ditemukan");
                        return;
                    }

                    const route = data.routes[0];

                    // 🔥 SIMPAN CACHE
                    routeCache[id] = route;

                    drawRoute(route, id);

                } catch (err) {
                    console.error(err);
                    alert("Gagal ambil rute");
                }
            };

            setTimeout(() => {
                map.invalidateSize();
            }, 300);

        });
    </script>
@endpush
