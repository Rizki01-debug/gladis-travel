@extends('layouts.app')

@section('content')

<h3 class="mb-3">📍 Tambah Meeting Point</h3>

{{-- 🔥 ERROR --}}
@if ($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('meeting-points.store') }}">
    @csrf

    {{-- CITY --}}
    <div class="mb-3">
        <label class="form-label">Kota</label>
        <select name="city_id" class="form-control" required>
            <option value="">-- Pilih Kota --</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- NAME --}}
    <div class="mb-3">
        <label class="form-label">Nama Titik</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    {{-- ADDRESS --}}
    <div class="mb-3">
        <label class="form-label">Alamat (Opsional)</label>
        <input type="text" name="address" class="form-control">
    </div>

    {{-- MAP --}}
    <div class="mb-3">
        <label class="form-label">Pilih Lokasi di Map (Klik untuk menentukan titik)</label>
        <div id="map" style="height: 400px; border-radius:10px;"></div>
    </div>

    {{-- LAT LONG --}}
    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Latitude</label>
            <input type="text" name="latitude" id="latitude" class="form-control" readonly required>
        </div>

        <div class="col-md-6 mb-3">
            <label>Longitude</label>
            <input type="text" name="longitude" id="longitude" class="form-control" readonly required>
        </div>
    </div>

    <button class="btn btn-success">
        💾 Simpan Meeting Point
    </button>

</form>

@endsection


{{-- ================= STYLES ================= --}}
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@endpush


{{-- ================= SCRIPTS ================= --}}
@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // 🔥 DEFAULT POSITION (BEKASI/JAKARTA)
    let defaultLat = -6.2;
    let defaultLng = 106.8;

    let map = L.map('map').setView([defaultLat, defaultLng], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    let marker = null;

    // 🔥 AUTO SET DEFAULT (optional)
    // document.getElementById('latitude').value = defaultLat;
    // document.getElementById('longitude').value = defaultLng;

    // ================= CLICK MAP =================
    map.on('click', function(e) {

        let lat = e.latlng.lat.toFixed(7);
        let lng = e.latlng.lng.toFixed(7);

        // 🔥 SET INPUT
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        // 🔥 REMOVE OLD MARKER
        if (marker) {
            map.removeLayer(marker);
        }

        // 🔥 ADD NEW MARKER
        marker = L.marker([lat, lng]).addTo(map)
            .bindPopup("📍 Lokasi dipilih")
            .openPopup();
    });

});
</script>
@endpush