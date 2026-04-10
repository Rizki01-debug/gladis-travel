@extends('layouts.app')

@section('content')

<h3>Tambah Meeting Point</h3>

<form method="POST" action="{{ route('meeting-points.store') }}">
    @csrf

    {{-- CITY --}}
    <div class="mb-3">
        <label>Kota</label>
        <select name="city_id" class="form-control" required>
            <option value="">-- Pilih Kota --</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- NAME --}}
    <div class="mb-2">
        <label>Nama Titik</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    {{-- ADDRESS --}}
    <div class="mb-2">
        <label>Alamat (Opsional)</label>
        <input type="text" name="address" class="form-control">
    </div>

    {{-- MAP --}}
    <div class="mb-3">
        <label>Pilih Lokasi di Map</label>
        <div id="map" style="height: 300px;"></div>
    </div>

    {{-- LATITUDE --}}
    <div class="mb-2">
        <label>Latitude</label>
        <input type="text" name="latitude" id="latitude" class="form-control" readonly required>
    </div>

    {{-- LONGITUDE --}}
    <div class="mb-3">
        <label>Longitude</label>
        <input type="text" name="longitude" id="longitude" class="form-control" readonly required>
    </div>

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

    var map = L.map('map').setView([-6.2, 106.8], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var marker;

    map.on('click', function(e) {
        var lat = e.latlng.lat.toFixed(7);
        var lng = e.latlng.lng.toFixed(7);

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        if (marker) {
            map.removeLayer(marker);
        }

        marker = L.marker([lat, lng]).addTo(map);
    });

});
</script>
@endpush