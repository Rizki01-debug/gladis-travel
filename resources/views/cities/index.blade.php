@extends('layouts.app')

@section('content')
    <h3 class="mb-3">📍 Data Kota</h3>

    <a href="{{ route('cities.create') }}" class="btn btn-primary mb-3">
        + Tambah Kota
    </a>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama Kota</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($cities as $i => $c)
                    <tr>
                        <td>{{ $i + 1 }}</td>

                        <td><b>{{ $c->name }}</b></td>

                        <td>{{ $c->latitude ?? '-' }}</td>
                        <td>{{ $c->longitude ?? '-' }}</td>

                        <td>
                            @if ($c->latitude && $c->longitude)
                                <span class="badge bg-success">Valid</span>
                            @else
                                <span class="badge bg-danger">Belum lengkap</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            🚫 Belum ada data kota
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 🔥 OPTIONAL: MAP PREVIEW --}}
    <h5 class="mt-4">🗺 Preview Lokasi Kota</h5>
    <div id="map" style="height: 400px;"></div>
@endsection


@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@endpush


@push('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            var map = L.map('map').setView([-2.5, 118], 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            var cities = @json($cities);

            var bounds = [];

            cities.forEach(function(c) {

                if (c.latitude && c.longitude) {

                    var lat = parseFloat(c.latitude);
                    var lng = parseFloat(c.longitude);

                    var marker = L.marker([lat, lng]).addTo(map);

                    marker.bindPopup(`<b>${c.name}</b>`);

                    bounds.push([lat, lng]);
                }
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds);
            }

        });
    </script>
@endpush
