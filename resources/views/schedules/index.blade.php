@extends('layouts.app')

@section('content')
    <h3>Jadwal Keberangkatan</h3>

    <a href="{{ route('schedules.create') }}" class="btn btn-primary mb-3">
        + Tambah Jadwal
    </a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Rute</th>
                <th>Kendaraan</th>
                <th>Jam</th>
                <th>Rute Map</th>
                <th>Jarak</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($schedules as $s)
                <tr>
                    <td>
                        {{ $s->origin->name ?? '-' }} → {{ $s->destination->name ?? '-' }}
                    </td>
                    <td>{{ $s->vehicle->name ?? '-' }}</td>
                    <td>{{ $s->departure_time }}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="showRoute({{ $s->id }})">
                            Lihat Rute
                        </button>
                    </td>

                    <td>
                        {{ $s->distance_km ?? 0 }} KM
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- MAP --}}
    <div id="map" style="height: 400px;" class="mt-4"></div>
@endsection


@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // 🔥 INIT MAP
            var map = L.map('map').setView([-6.2, 106.8], 8);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // 🔥 DATA DARI LARAVEL
            var schedules = @json($schedules);

            window.showRoute = function(scheduleId) {

                // 🔥 HAPUS MARKER & LINE (JANGAN HAPUS TILE)
                map.eachLayer(function(layer) {
                    if (layer instanceof L.Marker || layer instanceof L.Polyline) {
                        map.removeLayer(layer);
                    }
                });

                var schedule = schedules.find(s => s.id === scheduleId);

                if (!schedule || !schedule.route_points || schedule.route_points.length === 0) {
                    alert('Rute belum tersedia!');
                    return;
                }

                var latlngs = [];

                schedule.route_points.forEach(function(rp) {

                    var point = rp.meeting_point;

                    if (point && point.latitude && point.longitude) {

                        var lat = parseFloat(point.latitude);
                        var lng = parseFloat(point.longitude);

                        latlngs.push([lat, lng]);

                        L.marker([lat, lng])
                            .addTo(map)
                            .bindPopup(`<b>${point.name}</b>`);
                    }
                });

                if (latlngs.length > 0) {
                    var polyline = L.polyline(latlngs, {
                        weight: 5
                    }).addTo(map);

                    map.fitBounds(polyline.getBounds());
                }
            }

        });
    </script>
@endpush
