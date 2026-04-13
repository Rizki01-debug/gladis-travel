@extends('layouts.app')

@section('content')
    <h3 class="mb-3">🚐 Booking Kursi</h3>

    <form method="POST" action="{{ route('booking.store') }}">
        @csrf

        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

        {{-- ================= KURSI ================= --}}
        <h5>Pilih Kursi</h5>
        <div class="mb-3">
            @foreach ($seats as $seat)
                @php $isBooked = in_array($seat->id, $bookedSeats); @endphp

                <label class="btn m-1 {{ $isBooked ? 'btn-danger disabled' : 'btn-outline-primary' }}">
                    <input type="checkbox" name="seat_id[]" value="{{ $seat->id }}" {{ $isBooked ? 'disabled' : '' }}>
                    {{ $seat->seat_number }}
                </label>
            @endforeach
        </div>

        {{-- ================= PICKUP ================= --}}
        <h5>Pilih Jenis Pickup</h5>
        <select name="pickup_type" class="form-control mb-3" id="pickup_type">
            <option value="meeting_point">Meeting Point</option>
            <option value="pickup_location">Dijemput (Map)</option>
        </select>

        {{-- ================= MEETING POINT ================= --}}
        <div id="meeting_point_section">
            <select name="meeting_point_id" class="form-control mb-3" id="meeting_point_id">
                @foreach ($meetingPoints as $mp)
                    <option value="{{ $mp['id'] }}">{{ $mp['name'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- ================= MAP ================= --}}
        <div id="map_section">
            <h5>Map Route</h5>
            <div id="map" style="height:400px;"></div>

            <input type="hidden" name="pickup_maps" id="pickup_maps">
            <input type="hidden" name="distance_km" id="distance_km">
            <input type="hidden" name="price_estimation" id="price_estimation">

            <p>Jarak: <b><span id="distance_text">0</span> KM</b></p>
            <p>Harga: <b>Rp <span id="price_text">0</span></b></p>
        </div>

        <button class="btn btn-success mt-3">Booking</button>
    </form>
@endsection


@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
@endpush


@push('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const meetingPoints = @json($meetingPoints);

            const origin = L.latLng({{ $origin_lat }}, {{ $origin_lng }});
            const destination = L.latLng({{ $dest_lat }}, {{ $dest_lng }});

            const basePrice = {{ $tariff->base_price ?? 0 }};
            const tarif = {{ $tariff->price_per_km ?? 0 }};
            const pickupFee = {{ $tariff->pickup_fee ?? 0 }};

            let map = L.map('map').setView(origin, 10);

            // ================= MARKER MEETING POINT =================
            let markers = [];

            meetingPoints.forEach(point => {

                let marker = L.marker([point.latitude, point.longitude])
                    .addTo(map)
                    .bindPopup(point.name);

                // 🔥 SAAT DIKLIK
                marker.on('click', function() {

                    // set dropdown
                    document.getElementById('meeting_point_id').value = point.id;

                    // update route
                    let start = L.latLng(point.latitude, point.longitude);
                    drawRoute([start, destination]);

                });

                markers.push(marker);
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            let routingControl = null;
            let markerGroup = L.layerGroup().addTo(map);

            const pickupType = document.getElementById('pickup_type');
            const meetingSelect = document.getElementById('meeting_point_id');

            function clearMap() {
                if (routingControl) {
                    map.removeControl(routingControl);
                    routingControl = null;
                }
                markerGroup.clearLayers();
            }

            function drawMarkers(points) {
                points.forEach(p => {
                    L.marker(p).addTo(markerGroup);
                });
            }

            function drawRoute(latlngs, isPickup = false) {

                clearMap();

                drawMarkers(latlngs);

                routingControl = L.Routing.control({
                    waypoints: latlngs,
                    routeWhileDragging: false,
                    show: false,
                    addWaypoints: false
                }).addTo(map);

                routingControl.on('routesfound', function(e) {

                    let distance = e.routes[0].summary.totalDistance / 1000;

                    document.getElementById('distance_km').value = distance.toFixed(2);
                    document.getElementById('distance_text').innerText = distance.toFixed(2);

                    let price = basePrice + (distance * tarif);
                    if (isPickup) price += pickupFee;

                    document.getElementById('price_estimation').value = Math.round(price);
                    document.getElementById('price_text').innerText = Math.round(price);
                });

                map.fitBounds(L.latLngBounds(latlngs));
            }

            function getSelectedPoint() {
                return meetingPoints.find(p => p.id == meetingSelect.value);
            }

            function updateMeetingRoute() {
                let point = getSelectedPoint();
                if (!point) return;

                let start = L.latLng(point.latitude, point.longitude);

                drawRoute([start, destination]);
            }

            // INIT
            updateMeetingRoute();

            meetingSelect.addEventListener('change', updateMeetingRoute);

            map.on('click', function(e) {

                if (pickupType.value !== 'pickup_location') return;

                let user = L.latLng(e.latlng.lat, e.latlng.lng);

                let point = getSelectedPoint();
                if (!point) return;

                let start = L.latLng(point.latitude, point.longitude);

                drawRoute([start, user, destination], true);

                document.getElementById('pickup_maps').value =
                    e.latlng.lat + ',' + e.latlng.lng;
            });

            pickupType.addEventListener('change', function() {
                updateMeetingRoute();

                setTimeout(() => {
                    map.invalidateSize();
                }, 200);
            });

        });
    </script>
@endpush
