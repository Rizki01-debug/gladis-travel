@extends('layouts.app')

@section('content')
<div class="container">

    <h4 class="mb-4">✏️ Edit Jadwal</h4>

    {{-- GLOBAL ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <b>Terjadi kesalahan:</b>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('schedules.update', $schedule->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- ================= KOTA ================= --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Kota Asal</label>
                <select name="origin_city_id" class="form-control" required>
                    <option value="">-- Pilih Kota --</option>
                    @foreach ($cities as $c)
                        <option value="{{ $c->id }}"
                            {{ old('origin_city_id', $schedule->origin_city_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Kota Tujuan</label>
                <select name="destination_city_id" class="form-control" required>
                    <option value="">-- Pilih Kota --</option>
                    @foreach ($cities as $c)
                        <option value="{{ $c->id }}"
                            {{ old('destination_city_id', $schedule->destination_city_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ================= VEHICLE ================= --}}
        <div class="mb-3">
            <label class="form-label">Kendaraan</label>
            <select name="vehicle_id" class="form-control" required>
                <option value="">-- Pilih Kendaraan --</option>
                @foreach ($vehicles as $v)
                    <option value="{{ $v->id }}"
                        {{ old('vehicle_id', $schedule->vehicle_id) == $v->id ? 'selected' : '' }}>
                        {{ $v->name }} ({{ $v->plate_number ?? '-' }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ================= TIME ================= --}}
        <div class="mb-3">
            <label class="form-label">Jam Berangkat</label>
            <input type="time"
                   name="departure_time"
                   class="form-control"
                   value="{{ old('departure_time', \Carbon\Carbon::parse($schedule->departure_time)->format('H:i')) }}"
                   required>
        </div>

        {{-- ================= ROUTE ================= --}}
        <div class="mb-3">
            <label class="form-label">Rute Perjalanan (Meeting Points)</label>

            @php
                $selectedPoints = old('route_points', $schedule->routePoints->pluck('meeting_point_id')->toArray());
            @endphp

            <div class="border rounded p-3" style="max-height: 260px; overflow-y: auto;">

                @foreach ($meetingPoints as $point)
                    <div class="form-check d-flex align-items-center gap-2 mb-2">

                        {{-- ❌ TANPA NAME --}}
                        <input type="checkbox"
                               class="form-check-input route-checkbox"
                               value="{{ $point->id }}"
                               id="point{{ $point->id }}"
                               {{ in_array($point->id, $selectedPoints) ? 'checked' : '' }}>

                        <span class="badge bg-primary d-none order-badge"
                              id="order{{ $point->id }}">
                            #
                        </span>

                        <label class="form-check-label" for="point{{ $point->id }}">
                            <b>{{ $point->name }}</b>
                            <small class="text-muted">
                                ({{ $point->city->name ?? '-' }})
                            </small>
                        </label>
                    </div>
                @endforeach

            </div>

            {{-- 🔥 HIDDEN INPUT --}}
            <input type="hidden" name="route_points" id="routePointsInput">

            <small class="text-muted">
                Urutan mengikuti klik (titik awal → tujuan)
            </small>
        </div>

        {{-- ================= BUTTON ================= --}}
        <div class="d-flex gap-2">
            <button class="btn btn-success">
                💾 Update Jadwal
            </button>

            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                Kembali
            </a>
        </div>

    </form>
</div>

{{-- ================= SCRIPT FINAL ================= --}}
<script>
let selectedOrder = @json($selectedPoints ?? []);

const checkboxes = document.querySelectorAll('.route-checkbox');
const hiddenInput = document.getElementById('routePointsInput');

checkboxes.forEach(cb => {
    cb.addEventListener('change', function () {

        const value = this.value;

        if (this.checked) {
            if (!selectedOrder.includes(value)) {
                selectedOrder.push(value);
            }
        } else {
            selectedOrder = selectedOrder.filter(v => v != value);
        }

        updateUI();
    });
});

function updateUI() {

    document.querySelectorAll('.order-badge').forEach(b => {
        b.classList.add('d-none');
        b.innerText = '#';
    });

    selectedOrder.forEach((val, index) => {
        const badge = document.getElementById('order' + val);
        if (badge) {
            badge.classList.remove('d-none');
            badge.innerText = index + 1;
        }
    });

    hiddenInput.value = JSON.stringify(selectedOrder);
}

// 🔥 LOAD AWAL (EDIT MODE)
window.addEventListener('load', updateUI);
</script>

@endsection