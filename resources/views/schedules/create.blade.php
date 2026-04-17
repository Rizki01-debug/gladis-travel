@extends('layouts.app')

@section('content')
<div class="container">

    <h3 class="mb-4">➕ Tambah Jadwal</h3>

    {{-- ================= ALERT ERROR ================= --}}
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

    {{-- ================= FORM ================= --}}
    <form method="POST" action="{{ route('schedules.store') }}">
        @csrf

        {{-- ================= ORIGIN ================= --}}
        <div class="mb-3">
            <label class="form-label">Kota Asal</label>
            <select name="origin_city_id" class="form-control" required>
                <option value="">-- Pilih Kota Asal --</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}"
                        {{ old('origin_city_id') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ================= DESTINATION ================= --}}
        <div class="mb-3">
            <label class="form-label">Kota Tujuan</label>
            <select name="destination_city_id" class="form-control" required>
                <option value="">-- Pilih Kota Tujuan --</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}"
                        {{ old('destination_city_id') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ================= VEHICLE ================= --}}
        <div class="mb-3">
            <label class="form-label">Kendaraan</label>
            <select name="vehicle_id" class="form-control" required>
                <option value="">-- Pilih Kendaraan --</option>
                @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}"
                        {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ================= ROUTE ================= --}}
        <div class="mb-3">
            <label class="form-label">Pilih Rute (Meeting Points)</label>

            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                @forelse ($meetingPoints as $point)
                    <div class="form-check">
                        <input type="checkbox"
                            class="form-check-input"
                            name="route_points[]"
                            value="{{ $point->id }}"
                            id="point{{ $point->id }}"
                            {{ in_array($point->id, old('route_points', [])) ? 'checked' : '' }}>

                        <label class="form-check-label" for="point{{ $point->id }}">
                            {{ $point->name }}
                        </label>
                    </div>
                @empty
                    <p class="text-muted mb-0">Belum ada meeting point</p>
                @endforelse
            </div>

            <small class="text-muted">Pilih minimal 1 rute</small>
        </div>

        {{-- ================= TIME ================= --}}
        <div class="mb-3">
            <label class="form-label">Jam Keberangkatan</label>
            <input type="time"
                name="departure_time"
                class="form-control"
                value="{{ old('departure_time') }}"
                required>
        </div>

        {{-- ================= BUTTON ================= --}}
        <button class="btn btn-success w-100">
            💾 Simpan Jadwal
        </button>

    </form>
</div>
@endsection