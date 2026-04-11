@extends('layouts.app')

@section('content')

<h3>Tambah Kota</h3>

<form method="POST" action="{{ route('cities.store') }}">
    @csrf

    {{-- NAMA --}}
    <input type="text" name="name" class="form-control mb-3" placeholder="Nama Kota" required>

    {{-- LATITUDE --}}
    <input type="text" name="latitude" id="latitude" class="form-control mb-2" placeholder="Latitude" readonly>

    {{-- LONGITUDE --}}
    <input type="text" name="longitude" id="longitude" class="form-control mb-3" placeholder="Longitude" readonly>

    {{-- MAP --}}
    <div id="map" style="height: 400px;" class="mb-3"></div>

    <small class="text-muted">Klik map untuk memilih lokasi kota</small>

    <br><br>

    <button class="btn btn-success">Simpan</button>

</form>

@endsection


@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@endpush


@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // 🔥 INIT MAP
    var map = L.map('map').setView([-6.9, 107.6], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var marker;

    // 🔥 CLICK MAP
    map.on('click', function(e) {

        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        // isi input
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        // pindahkan marker
        if (marker) {
            map.removeLayer(marker);
        }

        marker = L.marker([lat, lng]).addTo(map);
    });

});
</script>
@endpush