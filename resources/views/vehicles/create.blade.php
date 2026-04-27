@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">🚐 Tambah Kendaraan</h4>
                <small class="text-muted">Tambahkan armada baru ke sistem</small>
            </div>

            <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
                ← Kembali
            </a>
        </div>

        {{-- ================= ALERT ERROR ================= --}}
        @if ($errors->any())
            <div class="alert alert-danger fade-in">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- ================= FORM ================= --}}
        <div class="card card-premium border-0">
            <div class="card-body">

                <form action="{{ route('vehicles.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">

                        {{-- NAMA --}}
                        <div class="col-md-6">
                            <label class="form-label">Nama Kendaraan</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Contoh: Toyota Hiace" value="{{ old('name') }}" required>

                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- PLAT --}}
                        <div class="col-md-6">
                            <label class="form-label">Plat Nomor</label>
                            <input type="text" name="plate_number"
                                class="form-control @error('plate_number') is-invalid @enderror"
                                placeholder="Contoh: B 1234 XYZ" value="{{ old('plate_number') }}" required>

                            @error('plate_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- KAPASITAS --}}
                        <div class="col-md-6">
                            <label class="form-label">Kapasitas Kursi</label>
                            <input type="number" name="seat_capacity"
                                class="form-control @error('seat_capacity') is-invalid @enderror" placeholder="Contoh: 12"
                                value="{{ old('seat_capacity') }}" min="1" required>

                            <small class="text-muted">Jumlah kursi akan otomatis dibuat</small>

                            @error('seat_capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    {{-- ================= ACTION ================= --}}
                    <div class="mt-4 d-flex gap-2">

                        <button type="submit" class="btn btn-success btn-premium">
                            💾 Simpan Kendaraan
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
