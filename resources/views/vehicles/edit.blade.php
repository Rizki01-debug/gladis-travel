@extends('layouts.app')

@section('content')
<div class="container-fluid fade-in">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">✏️ Edit Kendaraan</h4>
            <small class="text-muted">Perbarui data kendaraan</small>
        </div>

        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
            ← Kembali
        </a>
    </div>

    {{-- ================= ALERT ================= --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ================= FORM ================= --}}
    <div class="card card-premium border-0">
        <div class="card-body">

            <form action="{{ route('vehicles.update', $vehicle->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    {{-- NAMA --}}
                    <div class="col-md-6">
                        <label class="form-label">Nama Kendaraan</label>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $vehicle->name) }}" required>

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- PLAT --}}
                    <div class="col-md-6">
                        <label class="form-label">Plat Nomor</label>
                        <input type="text" name="plate_number"
                            class="form-control @error('plate_number') is-invalid @enderror"
                            value="{{ old('plate_number', $vehicle->plate_number) }}" required>

                        @error('plate_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- KAPASITAS --}}
                    <div class="col-md-6">
                        <label class="form-label">Kapasitas Kursi</label>
                        <input type="number" name="seat_capacity"
                            class="form-control @error('seat_capacity') is-invalid @enderror"
                            value="{{ old('seat_capacity', $vehicle->seat_capacity) }}"
                            min="1" required>

                        <small class="text-muted">
                            ⚠️ Jika diubah, kursi akan disesuaikan otomatis
                        </small>

                        @error('seat_capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- STATUS --}}
                    <div class="col-md-6">
                        <label class="form-label">Status</label>

                        <select name="status" class="form-control">
                            <option value="active" {{ $vehicle->status == 'active' ? 'selected' : '' }}>
                                Aktif
                            </option>
                            <option value="inactive" {{ $vehicle->status == 'inactive' ? 'selected' : '' }}>
                                Nonaktif
                            </option>
                        </select>
                    </div>

                </div>

                {{-- ACTION --}}
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary btn-premium">
                        💾 Update
                    </button>

                    <a href="{{ route('vehicles.index') }}" class="btn btn-light">
                        Batal
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection