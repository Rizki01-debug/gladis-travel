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
                {{-- ORIGIN --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kota Asal</label>
                    <select name="origin_city_id" class="form-control @error('origin_city_id') is-invalid @enderror"
                        required>

                        <option value="">-- Pilih Kota Asal --</option>

                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" {{ old('origin_city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('origin_city_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- DESTINATION --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kota Tujuan</label>
                    <select name="destination_city_id"
                        class="form-control @error('destination_city_id') is-invalid @enderror" required>

                        <option value="">-- Pilih Kota Tujuan --</option>

                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}"
                                {{ old('destination_city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('destination_city_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- ================= VEHICLE ================= --}}
            <div class="mb-3">
                <label class="form-label">Kendaraan</label>
                <select name="vehicle_id" class="form-control @error('vehicle_id') is-invalid @enderror" required>

                    <option value="">-- Pilih Kendaraan --</option>

                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->name }} ({{ $vehicle->plate_number }})
                        </option>
                    @endforeach
                </select>

                @error('vehicle_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ================= ROUTE ================= --}}
            <div class="mb-3">
                <label class="form-label">Rute Perjalanan (Meeting Points)</label>

                <div class="border rounded p-3" style="max-height: 220px; overflow-y: auto;">

                    @forelse ($meetingPoints as $point)
                        <div class="form-check mb-1">
                            <input type="checkbox" class="form-check-input" name="route_points[]"
                                value="{{ $point->id }}" id="point{{ $point->id }}"
                                {{ in_array($point->id, old('route_points', [])) ? 'checked' : '' }}>

                            <label class="form-check-label" for="point{{ $point->id }}">
                                <b>{{ $point->name }}</b>
                                <small class="text-muted">
                                    ({{ $point->city->name ?? '-' }})
                                </small>
                            </label>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada meeting point</div>
                    @endforelse

                </div>

                @error('route_points')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror

                <small class="text-muted">Pilih minimal 1 titik rute</small>
            </div>

            {{-- ================= TIME ================= --}}
            <div class="mb-3">
                <label class="form-label">Jam Keberangkatan</label>
                <input type="time" name="departure_time"
                    class="form-control @error('departure_time') is-invalid @enderror" value="{{ old('departure_time') }}"
                    required>

                @error('departure_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
@endsection
