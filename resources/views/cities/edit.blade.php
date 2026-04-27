@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="mb-4">
            <h4 class="fw-bold mb-1">✏️ Edit Kota</h4>
            <small class="text-muted">Perbarui lokasi dan informasi kota</small>
        </div>

        {{-- ================= CARD ================= --}}
        <div class="card card-premium border-0">
            <div class="card-body">

                {{-- ERROR --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <b>Terjadi kesalahan:</b>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('cities.update', $city->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- LEFT --}}
                        <div class="col-md-4">

                            {{-- NAMA --}}
                            <div class="mb-3">
                                <label class="form-label">Nama Kota</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $city->name) }}" required>
                            </div>

                            {{-- LAT --}}
                            <div class="mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="latitude" id="latitude" class="form-control"
                                    value="{{ old('latitude', $city->latitude) }}" readonly>
                            </div>

                            {{-- LNG --}}
                            <div class="mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="longitude" id="longitude" class="form-control"
                                    value="{{ old('longitude', $city->longitude) }}" readonly>
                            </div>

                            <small class="text-muted">
                                Klik atau geser marker pada peta
                            </small>

                            <div class="mt-4">
                                <button class="btn btn-primary btn-premium w-100">
                                    💾 Update Kota
                                </button>
                            </div>

                        </div>

                        {{-- RIGHT MAP --}}
                        <div class="col-md-8">
                            <div id="map" style="height:450px;border-radius:12px;"></div>
                        </div>

                    </div>

                </form>

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

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');

            let lat = parseFloat(latInput.value) || -6.9;
            let lng = parseFloat(lngInput.value) || 107.6;

            let map = L.map('map').setView([lat, lng], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            let marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);

            // 🔥 DRAG UPDATE
            marker.on('dragend', function(e) {
                let pos = e.target.getLatLng();
                latInput.value = pos.lat.toFixed(6);
                lngInput.value = pos.lng.toFixed(6);
            });

            // 🔥 CLICK MAP
            map.on('click', function(e) {
                let newLat = e.latlng.lat;
                let newLng = e.latlng.lng;

                latInput.value = newLat.toFixed(6);
                lngInput.value = newLng.toFixed(6);

                marker.setLatLng([newLat, newLng]);
            });

            // 🔥 FIX BUG MAP
            setTimeout(() => {
                map.invalidateSize();
            }, 200);

        });
    </script>
@endpush
