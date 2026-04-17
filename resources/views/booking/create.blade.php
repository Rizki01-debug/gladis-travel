@extends('layouts.app')

@section('content')
    <div class="container">

        <h3 class="mb-4">🚐 Booking Kursi</h3>

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

        <form method="POST" action="{{ route('booking.store') }}" onsubmit="return validateBooking()">
            @csrf

            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

            {{-- ================= DATE ================= --}}
            <div class="mb-3">
                <label class="form-label">Tanggal Keberangkatan</label>
                <input type="date" name="departure_date" class="form-control" value="{{ old('departure_date') }}"
                    min="{{ now()->addDays(3)->format('Y-m-d') }}" required>
                <small class="text-muted">Minimal H-3 dari hari ini</small>
            </div>

            {{-- ================= PHONE ================= --}}
            <div class="mb-3">
                <label class="form-label">📱 Nomor WhatsApp</label>
                <input type="text" name="phone" class="form-control" placeholder="Contoh: 628123456789"
                    value="{{ old('phone') }}" required>
            </div>

            {{-- ================= SEAT ================= --}}
            <h5>Pilih Kursi</h5>

            {{-- 🔥 LEGEND --}}
            <div class="mb-2">
                <span class="badge bg-dark">Driver</span>
                <span class="badge bg-danger">Terisi</span>
                <span class="badge border text-dark">Tersedia</span>
            </div>

            <div class="mb-3">

                @foreach ($seats as $seat)
                    @php
                        $isBooked = in_array($seat->id, $bookedSeats);
                        $isDriver = (bool) $seat->is_driver_seat;
                        $disabled = $isBooked || $isDriver;
                    @endphp

                    <label
                        class="btn m-1
            {{ $isDriver ? 'btn-dark text-white' : '' }}
            {{ $isBooked ? 'btn-danger text-white' : '' }}
            {{ !$disabled ? 'btn-outline-primary' : '' }}
            {{ $disabled ? 'opacity-50 disabled-seat' : '' }}">

                        <input type="checkbox" name="seat_id[]" value="{{ $seat->id }}" class="seat-checkbox"
                            {{ $disabled ? 'disabled' : '' }}>

                        {{ $seat->seat_number }}

                        @if ($isDriver)
                            <small>(Driver)</small>
                        @endif
                    </label>
                @endforeach

            </div>
            {{-- ================= PICKUP ================= --}}
            <div class="mb-3">
                <label class="form-label">Jenis Pickup</label>
                <select name="pickup_type" class="form-control" id="pickup_type">
                    <option value="meeting_point" {{ old('pickup_type') == 'meeting_point' ? 'selected' : '' }}>
                        Meeting Point
                    </option>
                    <option value="pickup_location" {{ old('pickup_type') == 'pickup_location' ? 'selected' : '' }}>
                        Dijemput (Map)
                    </option>
                </select>
            </div>

            {{-- ================= MEETING POINT ================= --}}
            <div id="meeting_point_section" class="mb-3">
                <label class="form-label">Pilih Meeting Point</label>
                <select name="meeting_point_id" class="form-control" id="meeting_point_id">
                    @forelse ($meetingPoints as $mp)
                        <option value="{{ $mp['id'] }}" {{ old('meeting_point_id') == $mp['id'] ? 'selected' : '' }}>
                            {{ $mp['name'] }}
                        </option>
                    @empty
                        <option value="">Belum ada meeting point</option>
                    @endforelse
                </select>
            </div>

            {{-- ================= MAP ================= --}}
            <div id="map_section" class="mb-3">
                <h5>📍 Pilih Lokasi Penjemputan</h5>
                <div id="map" style="height:400px;"></div>

                <input type="hidden" name="pickup_maps" id="pickup_maps" value="{{ old('pickup_maps') }}">

                <p class="mt-2">Jarak: <b><span id="distance_text">0</span> KM</b></p>
                <p>Harga: <b>Rp <span id="price_text">0</span></b></p>
            </div>

            <button class="btn btn-success mt-4 w-100">
                🚀 Booking Sekarang
            </button>

        </form>

    </div>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const meetingPoints = @json($meetingPoints ?? []);

        const origin = L.latLng({{ $origin_lat }}, {{ $origin_lng }});
        const destination = L.latLng({{ $dest_lat }}, {{ $dest_lng }});

        const basePrice = {{ $tariff->base_price ?? 0 }};
        const tarif = {{ $tariff->price_per_km ?? 0 }};
        const pickupFee = {{ $tariff->pickup_fee ?? 0 }};

        const pickupType = document.getElementById('pickup_type');
        const meetingSelect = document.getElementById('meeting_point_id');
        const mapSection = document.getElementById('map_section');
        const meetingSection = document.getElementById('meeting_point_section');

        // ================= MAP INIT =================
        let map = L.map('map').setView(origin, 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let routingControl = null;
        let markers = [];

        // ================= HELPER =================
        function clearRoute() {
            if (routingControl) {
                map.removeControl(routingControl);
                routingControl = null;
            }

            markers.forEach(m => map.removeLayer(m));
            markers = [];
        }

        function addMarker(latlng, text) {
            let marker = L.marker(latlng).addTo(map).bindPopup(text);
            markers.push(marker);
        }

        function formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function roundToThousand(num) {
            return Math.ceil(num / 1000) * 1000;
        }

        function getSelectedPoint() {
            return meetingPoints.find(p => p.id == meetingSelect.value);
        }

        // ================= DRAW ROUTE =================
        function drawRoute(latlngs, isPickup = false) {

            if (!latlngs || latlngs.length < 2) return;

            clearRoute();

            routingControl = L.Routing.control({
                waypoints: latlngs,
                routeWhileDragging: false,
                show: false,
                addWaypoints: false,
                draggableWaypoints: false
            }).addTo(map);

            // MARKERS
            latlngs.forEach((latlng, i) => {
                if (i === 0) addMarker(latlng, "Start");
                else if (i === latlngs.length - 1) addMarker(latlng, "Tujuan");
                else addMarker(latlng, "Pickup");
            });

            routingControl.on('routesfound', function(e) {

                let distance = e.routes[0].summary.totalDistance / 1000;

                document.getElementById('distance_text').innerText = distance.toFixed(2);

                let price = basePrice + (distance * tarif);
                if (isPickup) price += pickupFee;

                let finalPrice = roundToThousand(price);

                document.getElementById('price_text').innerText = formatRupiah(finalPrice);
            });

            map.fitBounds(L.latLngBounds(latlngs));
        }

        // ================= MEETING ROUTE =================
        function updateMeetingRoute() {
            let point = getSelectedPoint();
            if (!point) return;

            let start = L.latLng(point.latitude, point.longitude);

            drawRoute([start, destination]);
        }

        // ================= INIT LOAD =================
        setTimeout(() => {
            updateMeetingRoute();
            map.invalidateSize();
        }, 300);

        // ================= EVENT =================

        meetingSelect.addEventListener('change', updateMeetingRoute);

        map.on('click', function(e) {

            if (pickupType.value !== 'pickup_location') return;

            let user = L.latLng(e.latlng.lat, e.latlng.lng);
            let point = getSelectedPoint();

            if (!point) {
                alert('Pilih meeting point dulu!');
                return;
            }

            let start = L.latLng(point.latitude, point.longitude);

            drawRoute([start, user, destination], true);

            document.getElementById('pickup_maps').value =
                e.latlng.lat + ',' + e.latlng.lng;
        });

        pickupType.addEventListener('change', function() {

            const isMeeting = this.value === 'meeting_point';

            meetingSection.style.display = isMeeting ? 'block' : 'none';
            mapSection.style.display = 'block'; // 🔥 selalu tampil

            clearRoute();

            if (isMeeting) {
                updateMeetingRoute();
            }

            setTimeout(() => map.invalidateSize(), 300);
        });

    });
</script>
