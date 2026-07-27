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

            {{-- ================= 🔥 METODE PEMBAYARAN ================= --}}
            <div class="mb-4">
                <h5 class="fw-bold mb-3">💳 Pilih Metode Pembayaran</h5>
                
                <div class="row">
                    {{-- Online Payment --}}
                    <div class="col-md-12 mb-2">
                        <div class="payment-method border rounded p-3 {{ old('payment_method') == 'online' ? 'border-primary bg-light' : '' }}">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       value="online" id="payment_online" checked>
                                <label class="form-check-label w-100" for="payment_online">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-credit-card text-primary me-2"></i>
                                            <strong>Bayar Online</strong>
                                            <br>
                                            <small class="text-muted">Via Midtrans (Kartu Kredit, Bank Transfer, QRIS, E-Wallet)</small>
                                        </span>
                                        <span class="badge bg-primary">Midtrans</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Cash / Bayar di Tempat --}}
                    {{-- <div class="col-md-6 mb-2">
                        <div class="payment-method border rounded p-3 {{ old('payment_method') == 'cash' ? 'border-primary bg-light' : '' }}">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       value="cash" id="payment_cash">
                                <label class="form-check-label w-100" for="payment_cash">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-money-bill-wave text-success me-2"></i>
                                            <strong>Bayar di Tempat</strong>
                                            <br>
                                            <small class="text-muted">Bayar langsung ke driver saat naik</small>
                                        </span>
                                        <span class="badge bg-success">Cash</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div> --}}
                </div>

                {{-- Info tambahan --}}
                <div class="alert alert-info mt-2" id="payment_info_online">
                    <i class="fas fa-info-circle me-2"></i>
                    Anda akan diarahkan ke halaman pembayaran setelah booking berhasil.
                </div>
                {{-- <div class="alert alert-warning mt-2 d-none" id="payment_info_cash">
                    <i class="fas fa-info-circle me-2"></i>
                    Pembayaran dilakukan langsung kepada driver saat naik kendaraan.
                </div> --}}
            </div>

            <button class="btn btn-success w-100">
                🚀 Booking Sekarang
            </button>

        </form>
    </div>
@endsection

@push('styles')
<style>
    .payment-method {
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
    }
    .payment-method:hover {
        border-color: #0d6efd !important;
        background-color: #f8f9fa;
    }
    .payment-method:has(input[type="radio"]:checked) {
        border-color: #0d6efd !important;
        background-color: #e7f1ff !important;
    }
    .payment-method label {
        cursor: pointer;
        margin: 0;
    }
    .payment-method input[type="radio"] {
        margin-top: 0.5rem;
    }
    .seat-box {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-weight: bold;
        cursor: default;
        border: 2px solid #ddd;
        background: #f8f9fa;
        transition: 0.2s;
    }
    .seat-available {
        cursor: pointer;
        border-color: #198754;
        background: #d1e7dd;
    }
    .seat-available:hover {
        background: #a3cfbb;
        transform: scale(1.05);
    }
    .seat-checkbox {
        display: none;
    }
    .seat-checkbox:checked + .seat-available {
        background: #0d6efd;
        border-color: #0d6efd;
        color: white;
        transform: scale(1.05);
    }
    .seat-disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .seat-driver {
        background: #f8d7da;
        border-color: #dc3545;
        color: #dc3545;
    }
    .seat-booked {
        background: #e2e3e5;
        border-color: #6c757d;
        color: #6c757d;
    }
</style>
@endpush

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

            // 🔥 Payment method elements
            const paymentOnline = document.getElementById('payment_online');
            const paymentCash = document.getElementById('payment_cash');
            const infoOnline = document.getElementById('payment_info_online');
            const infoCash = document.getElementById('payment_info_cash');

            let map = L.map('map').setView(origin, 9);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            let routingControl = null;
            let markers = [];

            // ================= PAYMENT METHOD TOGGLE =================
            function togglePaymentInfo() {
                if (paymentOnline.checked) {
                    infoOnline.classList.remove('d-none');
                    infoCash.classList.add('d-none');
                } else {
                    infoOnline.classList.add('d-none');
                    infoCash.classList.remove('d-none');
                }
            }

            paymentOnline.addEventListener('change', togglePaymentInfo);
            paymentCash.addEventListener('change', togglePaymentInfo);

            // Click on payment method card
            document.querySelectorAll('.payment-method').forEach(function(card) {
                card.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        togglePaymentInfo();
                    }
                });
            });

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

        // ================= VALIDATION =================
        function validateBooking() {
            const seats = document.querySelectorAll('.seat-checkbox:checked');
            if (seats.length === 0) {
                alert('Pilih minimal 1 kursi!');
                return false;
            }

            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) {
                alert('Pilih metode pembayaran!');
                return false;
            }

            const distance = document.getElementById('distance_km').value;
            if (!distance || distance <= 0) {
                alert('Jarak tidak valid!');
                return false;
            }

            return true;
        }
    </script>
@endpush