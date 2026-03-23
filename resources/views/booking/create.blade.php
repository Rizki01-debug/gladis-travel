@extends('layouts.app')

@section('content')
    <h3>Booking Kursi</h3>

    <form method="POST" action="{{ route('booking.store') }}">
        @csrf

        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

        {{-- ===================== KURSI ===================== --}}
        <h5>Pilih Kursi</h5>

        <div class="mb-3">
            @foreach ($seats as $seat)
                <label class="btn btn-outline-primary m-1">
                    <input type="radio" name="seat_id" value="{{ $seat->id }}" required>
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

            <p class="mt-2">Jarak: <span id="distance_text">0</span> km</p>
            <p>Estimasi Harga: Rp <span id="price_text">0</span></p>
        </div>

        <button class="btn btn-success">Booking</button>

    </form>
@endsection

@section('scripts')
    <script>
        // 🔥 TARIF DARI DATABASE
        var tarif = {{ $tariff->price_per_km }};
    </script>

    <script>
        // ================= TOGGLE UI =================
        document.getElementById('pickup_type').addEventListener('change', function() {
            let type = this.value;

            if (type === 'pickup_location') {
                document.getElementById('map_section').style.display = 'block';
                document.getElementById('meeting_point_section').style.display = 'none';

                setTimeout(() => {
                    map.invalidateSize();
                }, 200);

            } else {
                document.getElementById('map_section').style.display = 'none';
                document.getElementById('meeting_point_section').style.display = 'block';
            }
        });

        // ================= MAP =================
        var map = L.map('map').setView([-6.9, 107.6], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var routingControl = null;
        var origin = L.latLng(-6.9, 107.6);

        // 🔥 CLICK MAP
        map.on('click', function(e) {

            var destination = L.latLng(e.latlng.lat, e.latlng.lng);

            if (routingControl !== null) {
                map.removeControl(routingControl);
            }

            routingControl = L.Routing.control({
                waypoints: [origin, destination],
                routeWhileDragging: false,
                show: false
            }).addTo(map);

            routingControl.on('routesfound', function(e) {

                var route = e.routes[0];
                var distance = route.summary.totalDistance / 1000;

                // SET DATA
                document.getElementById('distance_km').value = distance.toFixed(2);
                document.getElementById('distance_text').innerText = distance.toFixed(2);

                var price = distance * tarif;

                document.getElementById('price_estimation').value = price;
                document.getElementById('price_text').innerText = Math.round(price);

            });

            document.getElementById('pickup_maps').value =
                e.latlng.lat + ',' + e.latlng.lng;
        });
    </script>
@endsection
