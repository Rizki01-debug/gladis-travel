@extends('layouts.app')

@section('content')
    <h3 class="mb-3">🚐 Booking Kursi</h3>

    <form method="POST" action="{{ route('booking.store') }}">
        @csrf

        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

        {{-- ===================== KURSI ===================== --}}
        <h5>Pilih Kursi</h5>

        <div class="mb-3">
            @foreach ($seats as $seat)
                @php
                    $isBooked = in_array($seat->id, $bookedSeats);
                @endphp

                <label class="btn m-1 {{ $isBooked ? 'btn-danger disabled' : 'btn-outline-primary' }}">
                    <input type="radio" name="seat_id" value="{{ $seat->id }}" {{ $isBooked ? 'disabled' : '' }}
                        required>

                    {{ $seat->seat_number }}
                </label>
            @endforeach
        </div>

        {{-- ===================== PICKUP TYPE ===================== --}}
        <h5>Pilih Jenis Pickup</h5>

        <select name="pickup_type" class="form-control mb-3" id="pickup_type">
            <option value="meeting_point">Meeting Point</option>
            <option value="pickup_location">Dijemput (Map)</option>
        </select>

        {{-- ===================== MEETING POINT ===================== --}}
        <div id="meeting_point_section">
            <select name="meeting_point_id" class="form-control mb-3">
                @foreach ($meetingPoints as $mp)
                    <option value="{{ $mp->id }}">{{ $mp->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- ===================== MAP ===================== --}}
        <div id="map_section" style="display:none;">
            <h5>Pilih Lokasi di Map</h5>

            <div id="map" style="height: 400px;"></div>

            <input type="hidden" name="pickup_maps" id="pickup_maps">
            <input type="hidden" name="distance_km" id="distance_km">
            <input type="hidden" name="price_estimation" id="price_estimation">

            <p class="mt-2">Jarak: <b><span id="distance_text">0</span> km</b></p>
            <p>Estimasi Harga: <b>Rp <span id="price_text">0</span></b></p>
        </div>

        <button type="submit" class="btn btn-success mt-3">Booking</button>
    </form>
@endsection


@section('scripts')
    <script>
        // 🔥 TARIF (AMAN)
        var basePrice = {{ $tariff->base_price ?? 0 }};
        var tarif = {{ $tariff->price_per_km ?? 0 }};
        var pickupFee = {{ $tariff->pickup_fee ?? 0 }};
    </script>

    <script>
        // ================= INIT MAP =================
        var map = L.map('map').setView([-6.9, 107.6], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        let routingControl = null;

        // 🔥 TITIK
        var meetingPoint = L.latLng(-6.9, 107.6); // nanti bisa dinamis
        var destination = L.latLng(-6.2, 106.8); // nanti bisa dari schedule

        // ================= DRAW ROUTE =================
        function drawRoute(userLocation = null) {

            if (routingControl !== null) {
                map.removeControl(routingControl);
            }

            let waypoints = [];

            // 🔥 MEETING POINT ONLY
            if (!userLocation) {
                waypoints = [meetingPoint, destination];
            }
            // 🔥 PICKUP MODE
            else {
                waypoints = [meetingPoint, userLocation, destination];
            }

            routingControl = L.Routing.control({
                waypoints: waypoints,
                routeWhileDragging: false,
                show: false
            }).addTo(map);

            routingControl.on('routesfound', function(e) {

                let route = e.routes[0];
                let distance = route.summary.totalDistance / 1000;

                document.getElementById('distance_km').value = distance.toFixed(2);
                document.getElementById('distance_text').innerText = distance.toFixed(2);

                let price = basePrice + (distance * tarif);

                if (userLocation) {
                    price += pickupFee;
                }

                document.getElementById('price_estimation').value = Math.round(price);
                document.getElementById('price_text').innerText = Math.round(price);
            });
        }

        // ================= TOGGLE =================
        const pickupType = document.getElementById('pickup_type');
        const mapSection = document.getElementById('map_section');
        const meetingSection = document.getElementById('meeting_point_section');

        pickupType.addEventListener('change', function() {

            if (this.value === 'pickup_location') {
                mapSection.style.display = 'block';
                meetingSection.style.display = 'none';

                setTimeout(() => map.invalidateSize(), 200);

            } else {
                mapSection.style.display = 'none';
                meetingSection.style.display = 'block';

                // 🔥 langsung tampil route meeting → tujuan
                drawRoute(null);
            }
        });

        // ================= CLICK MAP =================
        map.on('click', function(e) {

            let userLocation = L.latLng(e.latlng.lat, e.latlng.lng);

            drawRoute(userLocation);

            document.getElementById('pickup_maps').value =
                e.latlng.lat + ',' + e.latlng.lng;
        });

        // 🔥 INIT DEFAULT ROUTE
        drawRoute(null);
    </script>
@endsection
