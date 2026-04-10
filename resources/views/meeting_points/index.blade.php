@extends('layouts.app')

@section('content')

<h3>Data Meeting Points</h3>

<div class="mb-3">
    <a href="{{ route('meeting-points.create') }}" class="btn btn-primary">
        + Tambah Titik
    </a>
</div>

{{-- MAP --}}
<div id="map" style="height: 400px;" class="mb-4 rounded shadow"></div>

{{-- TABLE --}}
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Kota</th>
            <th>Latitude</th>
            <th>Longitude</th>
        </tr>
    </thead>
    <tbody>
        @foreach($points as $point)
            <tr>
                <td>{{ $point->name }}</td>
                <td>{{ $point->city->name ?? '-' }}</td>
                <td>{{ $point->latitude }}</td>
                <td>{{ $point->longitude }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection


@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // 🔥 INIT MAP
    var map = L.map('map').setView([-6.2, 106.8], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // 🔥 DATA
    var points = @json($points);

    var bounds = [];
    var latlngs = []; // 🔥 untuk garis rute

    // 🔥 SORT biar tidak random (optional tapi penting)
    points.sort((a, b) => a.id - b.id);

    points.forEach(function(point) {

        if (point.latitude && point.longitude) {

            var lat = parseFloat(point.latitude);
            var lng = parseFloat(point.longitude);

            // 🔥 MARKER
            var marker = L.marker([lat, lng]).addTo(map);

            marker.bindPopup(`
                <b>${point.name}</b><br>
                Kota: ${point.city ? point.city.name : '-'}
            `);

            bounds.push([lat, lng]);
            latlngs.push([lat, lng]); // 🔥 simpan untuk polyline
        }
    });

    // 🔥 POLYLINE (GARIS RUTE)
    if (latlngs.length > 1) {
        L.polyline(latlngs, {
            color: 'blue',
            weight: 4,
            opacity: 0.7
        }).addTo(map);
    }

    // 🔥 AUTO ZOOM
    if (bounds.length > 0) {
        map.fitBounds(bounds);
    }

});
</script>
@endpush