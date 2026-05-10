@extends('layouts.app')

@section('content')
<div class="container">

    <h4 class="mb-4">➕ Tambah Jadwal</h4>

    {{-- ================= GLOBAL ERROR ================= --}}
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

    <form method="POST" action="{{ route('schedules.store') }}">
        @csrf

        {{-- ================= KOTA ================= --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Kota Asal</label>
                <select name="origin_city_id" class="form-control" required>
                    <option value="">-- Pilih Kota Asal --</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" {{ old('origin_city_id') == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Kota Tujuan</label>
                <select name="destination_city_id" class="form-control" required>
                    <option value="">-- Pilih Kota Tujuan --</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" {{ old('destination_city_id') == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
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
                @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->name }} ({{ $vehicle->plate_number }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ================= ROUTE ================= --}}
        <div class="mb-3">
            <label class="form-label">Rute Perjalanan (Meeting Points)</label>

            <div class="border rounded p-3" style="max-height: 260px; overflow-y: auto;">

                @foreach ($meetingPoints as $point)
                    <div class="form-check mb-2 d-flex align-items-center gap-2">

                        <input type="checkbox"
                               class="form-check-input route-checkbox"
                               value="{{ $point->id }}"
                               id="point{{ $point->id }}">

                        <span class="badge bg-primary d-none order-badge" id="order{{ $point->id }}">
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

            {{-- 🔥 HIDDEN INPUT (INI KUNCI NYA) --}}
            <input type="hidden" name="route_points" id="routePointsInput">

            <small class="text-muted">
                Urutan otomatis mengikuti klik
            </small>
        </div>

        {{-- ================= TIME ================= --}}
        <div class="mb-3">
            <label class="form-label">Jam Keberangkatan</label>
            <input type="time" name="departure_time" class="form-control" required>
        </div>

        {{-- ================= BUTTON ================= --}}
        <div class="d-flex gap-2">
            <button class="btn btn-success">
                💾 Simpan Jadwal
            </button>

            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                Kembali
            </a>
        </div>

    </form>
</div>

{{-- ================= SCRIPT FINAL ================= --}}
<script>
let selectedOrder = [];

const checkboxes = document.querySelectorAll('.route-checkbox');
const hiddenInput = document.getElementById('routePointsInput');

checkboxes.forEach(cb => {
    cb.addEventListener('change', function () {

        const value = this.value;

        if (this.checked) {
            // tambahkan ke urutan klik
            selectedOrder.push(value);
        } else {
            // hapus dari array
            selectedOrder = selectedOrder.filter(v => v !== value);
        }

        updateUI();
    });
});

function updateUI() {

    // reset semua badge
    document.querySelectorAll('.order-badge').forEach(b => {
        b.classList.add('d-none');
        b.innerText = '#';
    });

    // set ulang berdasarkan urutan klik
    selectedOrder.forEach((val, index) => {
        const badge = document.getElementById('order' + val);
        badge.classList.remove('d-none');
        badge.innerText = index + 1;
    });

    // 🔥 kirim ke backend (JSON)
    hiddenInput.value = JSON.stringify(selectedOrder);
}
</script>

@endsection