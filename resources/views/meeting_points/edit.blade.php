@extends('layouts.app')

@section('content')

<h3 class="mb-3">✏️ Edit Meeting Point</h3>

@if ($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('meeting-points.update', $point->id) }}">
    @csrf
    @method('PUT')

    {{-- CITY --}}
    <div class="mb-3">
        <label class="form-label">Kota</label>
        <select name="city_id" class="form-control" required>
            @foreach($cities as $city)
                <option value="{{ $city->id }}"
                    {{ $point->city_id == $city->id ? 'selected' : '' }}>
                    {{ $city->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- NAME --}}
    <div class="mb-3">
        <label class="form-label">Nama Titik</label>
        <input type="text" name="name" class="form-control"
            value="{{ $point->name }}" required>
    </div>

    {{-- ADDRESS --}}
    <div class="mb-3">
        <label class="form-label">Alamat</label>
        <input type="text" name="address" class="form-control"
            value="{{ $point->address }}">
    </div>

    {{-- MAP --}}
    <div class="mb-3">
        <label class="form-label">Edit Lokasi di Map</label>
        <div id="map" style="height:400px;"></div>
    </div>

    {{-- LAT LONG --}}
    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Latitude</label>
            <input type="text" name="latitude" id="latitude"
                class="form-control"
                value="{{ $point->latitude }}" readonly required>
        </div>

        <div class="col-md-6 mb-3">
            <label>Longitude</label>
            <input type="text" name="longitude" id="longitude"
                class="form-control"
                value="{{ $point->longitude }}" readonly required>
        </div>
    </div>

    <button class="btn btn-primary">
        💾 Update Meeting Point
    </button>

</form>

@endsection


@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@endpush


@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let lat = {{ $point->latitude ?? -6.2 }};
    let lng = {{ $point->longitude ?? 106.8 }};

    let map = L.map('map').setView([lat, lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
        .addTo(map);

    let marker = L.marker([lat, lng]).addTo(map);

    // 🔥 CLICK MAP
    map.on('click', function(e) {

        let newLat = e.latlng.lat.toFixed(7);
        let newLng = e.latlng.lng.toFixed(7);

        document.getElementById('latitude').value = newLat;
        document.getElementById('longitude').value = newLng;

        map.removeLayer(marker);

        marker = L.marker([newLat, newLng]).addTo(map);
    });

});
</script>
@endpush