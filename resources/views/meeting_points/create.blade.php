@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="mb-4">
            <h4 class="fw-bold mb-1">📍 Tambah Meeting Point</h4>
            <small class="text-muted">Tambahkan titik penjemputan baru</small>
        </div>

        {{-- ================= ERROR ================= --}}
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

        {{-- ================= FORM ================= --}}
        <div class="card card-premium border-0">
            <div class="card-body">

                <form method="POST" action="{{ route('meeting-points.store') }}">
                    @csrf

                    <div class="row">

                        {{-- CITY --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kota</label>
                            <select name="city_id" id="city_select" class="form-control" required>
                                <option value="">-- Pilih Kota --</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" data-lat="{{ $city->latitude }}"
                                        data-lng="{{ $city->longitude }}"
                                        {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- NAME --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Titik</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                    </div>

                    {{-- ADDRESS --}}
                    <div class="mb-3">
                        <label class="form-label">Alamat (Opsional)</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    </div>

                    {{-- MAP --}}
                    <div class="mb-3">
                        <label class="form-label">Pilih Lokasi di Map</label>
                        <div id="map" style="height: 400px; border-radius:12px;"></div>
                        <small class="text-muted">Klik pada map untuk menentukan titik</small>
                    </div>

                    {{-- LAT LONG --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Latitude</label>
                            <input type="text" name="latitude" id="latitude" class="form-control"
                                value="{{ old('latitude') }}" readonly required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Longitude</label>
                            <input type="text" name="longitude" id="longitude" class="form-control"
                                value="{{ old('longitude') }}" readonly required>
                        </div>
                    </div>

                    {{-- BUTTON --}}
                    <div class="mt-3">
                        <button class="btn btn-success btn-premium">
                            💾 Simpan Meeting Point
                        </button>
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

            const citySelect = document.getElementById('city_select');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');

            // 🔥 DEFAULT INDONESIA
            let map = L.map('map').setView([-2.5, 118], 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            let marker = null;

            // ================= SET MARKER =================
            function setMarker(lat, lng) {

                lat = parseFloat(lat);
                lng = parseFloat(lng);

                if (isNaN(lat) || isNaN(lng)) return;

                latInput.value = lat.toFixed(7);
                lngInput.value = lng.toFixed(7);

                if (marker) {
                    map.removeLayer(marker);
                }

                marker = L.marker([lat, lng]).addTo(map)
                    .bindPopup("📍 Lokasi dipilih")
                    .openPopup();

                map.setView([lat, lng], 13);
            }

            // ================= CLICK MAP =================
            map.on('click', function(e) {
                setMarker(e.latlng.lat, e.latlng.lng);
            });

            // ================= CHANGE CITY =================
            citySelect.addEventListener('change', function() {

                let selected = this.options[this.selectedIndex];

                let lat = selected.getAttribute('data-lat');
                let lng = selected.getAttribute('data-lng');

                if (lat && lng) {
                    setMarker(lat, lng);
                }
            });

            // ================= OLD VALUE (EDIT / ERROR) =================
            if (latInput.value && lngInput.value) {
                setMarker(latInput.value, lngInput.value);
            }

            // 🔥 FIX MAP BLANK
            setTimeout(() => {
                map.invalidateSize();
            }, 300);

        });
    </script>
@endpush
