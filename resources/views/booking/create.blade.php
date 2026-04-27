@extends('layouts.app')

@section('content')
    <div class="container">

        <h3 class="mb-4">🚐 Booking Kursi</h3>

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

        <form method="POST" action="{{ route('booking.store') }}" onsubmit="return validateBooking()">
            @csrf

            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

            {{-- 🔥 SYNC BACKEND --}}
            <input type="hidden" name="distance_km" id="distance_km">
            <input type="hidden" name="price_total" id="price_total">

            {{-- DATE --}}
            <div class="mb-3">
                <label class="form-label">📅 Tanggal Keberangkatan</label>
                <input type="date" name="departure_date" class="form-control"
                    min="{{ now()->addDays(3)->format('Y-m-d') }}" required>
                <small class="text-muted">Minimal H-3</small>
            </div>

            {{-- PHONE --}}
            <div class="mb-3">
                <label class="form-label">📱 Nomor WhatsApp</label>
                <input type="text" name="phone" class="form-control" placeholder="628xxxx" required>
            </div>

            {{-- ================= SEAT ================= --}}
            <h5 class="fw-bold mb-3">💺 Pilih Kursi</h5>

            <div class="mb-3 d-flex flex-wrap">

                @foreach ($seats as $seat)
                    @php
                        $isDriver = $seat->seat_number == 1;
                        $isBooked = in_array($seat->id, $bookedSeatIds ?? []);
                        $disabled = $isDriver || $isBooked;
                        $id = 'seat_' . $seat->id;
                    @endphp

                    <div class="m-1">

                        @if ($disabled)
                            <div
                                class="seat-box seat-disabled
                {{ $isDriver ? 'seat-driver' : 'seat-booked' }}">
                                {{ $seat->seat_number }}
                            </div>
                        @else
                            <input type="checkbox" id="{{ $id }}" name="seat_id[]" value="{{ $seat->id }}"
                                class="seat-checkbox">

                            <label for="{{ $id }}" class="seat-box seat-available">
                                {{ $seat->seat_number }}
                            </label>
                        @endif

                    </div>
                @endforeach

            </div>

            {{-- PICKUP --}}
            <div class="mb-3">
                <label>Jenis Pickup</label>
                <select name="pickup_type" id="pickup_type" class="form-control">
                    <option value="meeting_point">Meeting Point</option>
                    <option value="pickup_location">Dijemput</option>
                </select>
            </div>

            {{-- MEETING --}}
            <div class="mb-3">
                <label>Meeting Point</label>
                <select name="meeting_point_id" id="meeting_point_id" class="form-control">
                    @foreach ($meetingPoints as $mp)
                        <option value="{{ $mp['id'] }}">
                            {{ $mp['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- MAP --}}
            <div class="mb-3">
                <h5>🗺️ Lokasi</h5>
                <div id="map" style="height:400px;border-radius:12px;"></div>

                <input type="hidden" name="pickup_maps" id="pickup_maps">

                <div class="mt-2">
                    <b>Jarak:</b> <span id="distance_text">-</span> KM <br>
                    <b>Harga:</b> Rp <span id="price_text">-</span>
                </div>
            </div>

            <button class="btn btn-success w-100">
                🚀 Booking Sekarang
            </button>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const meetingPoints = @json($meetingPoints);
            const origin = L.latLng({{ $origin_lat }}, {{ $origin_lng }});
            const destination = L.latLng({{ $dest_lat }}, {{ $dest_lng }});

            const basePrice = {{ $tariff->base_price }};
            const tarif = {{ $tariff->price_per_km }};
            const pickupFee = {{ $tariff->pickup_fee }};

            const pickupType = document.getElementById('pickup_type');
            const meetingSelect = document.getElementById('meeting_point_id');

            const distanceText = document.getElementById('distance_text');
            const priceText = document.getElementById('price_text');
            const distanceInput = document.getElementById('distance_km');
            const priceInput = document.getElementById('price_total');
            const pickupMaps = document.getElementById('pickup_maps');

            let map = L.map('map').setView(origin, 9);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            let routingControl = null;
            let markers = [];

            // ================= CLEAR =================
            function clearRoute() {
                if (routingControl) {
                    map.removeControl(routingControl);
                    routingControl = null;
                }

                markers.forEach(m => map.removeLayer(m));
                markers = [];
            }

            // ================= MARKER =================
            function addMarker(latlng, label) {
                let marker = L.marker(latlng)
                    .addTo(map)
                    .bindPopup(label);

                markers.push(marker);
            }

            // ================= FORMAT =================
            function formatRupiah(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }

            function round(num) {
                return Math.ceil(num / 1000) * 1000;
            }

            function getPoint() {
                return meetingPoints.find(p => p.id == meetingSelect.value);
            }

            // ================= DRAW ROUTE =================
            function drawRoute(points, isPickup = false) {

                if (!points || points.length < 2) return;

                clearRoute();

                routingControl = L.Routing.control({
                    waypoints: points,
                    show: false,
                    addWaypoints: false,
                    routeWhileDragging: false,
                    draggableWaypoints: false,
                    createMarker: () => null
                }).addTo(map);

                routingControl.on('routesfound', function(e) {

                    let route = e.routes[0];
                    let distance = route.summary.totalDistance / 1000;

                    let price = basePrice + (distance * tarif);
                    if (isPickup) price += pickupFee;

                    let finalPrice = round(price);

                    // ===== UI =====
                    distanceText.innerText = distance.toFixed(2);
                    priceText.innerText = formatRupiah(finalPrice);

                    // ===== SYNC BACKEND =====
                    if (distanceInput) distanceInput.value = distance.toFixed(2);
                    if (priceInput) priceInput.value = finalPrice;

                    // ===== MARKERS =====
                    addMarker(points[0], "📍 Start");
                    addMarker(points[points.length - 1], "🏁 Tujuan");

                    if (points.length === 3) {
                        addMarker(points[1], "🚗 Pickup");
                    }

                    // ===== AUTO ZOOM =====
                    map.fitBounds(L.latLngBounds(route.coordinates), {
                        padding: [40, 40]
                    });
                });
            }

            // ================= INIT =================
            setTimeout(() => {
                let p = getPoint();
                if (p) {
                    drawRoute([L.latLng(p.latitude, p.longitude), destination]);
                }
                map.invalidateSize();
            }, 300);

            // ================= CHANGE MEETING =================
            meetingSelect.addEventListener('change', () => {
                let p = getPoint();
                if (p) {
                    drawRoute([L.latLng(p.latitude, p.longitude), destination]);
                }
            });

            // ================= CHANGE PICKUP TYPE =================
            pickupType.addEventListener('change', () => {

                clearRoute();
                pickupMaps.value = '';

                let p = getPoint();
                if (!p) return;

                if (pickupType.value === 'meeting_point') {
                    drawRoute([L.latLng(p.latitude, p.longitude), destination]);
                }
            });

            // ================= CLICK MAP =================
            map.on('click', function(e) {

                if (pickupType.value !== 'pickup_location') return;

                let p = getPoint();
                if (!p) return alert('Pilih meeting point!');

                let start = L.latLng(p.latitude, p.longitude);
                let user = L.latLng(e.latlng.lat, e.latlng.lng);

                drawRoute([start, user, destination], true);

                pickupMaps.value = e.latlng.lat + ',' + e.latlng.lng;
            });

        });
    </script>
@endpush
