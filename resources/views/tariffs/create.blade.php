@extends('layouts.app')

@section('content')
    <div class="container">

        <h4 class="mb-4">➕ Tambah Tarif</h4>

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

        <form action="{{ route('tariffs.store') }}" method="POST">
            @csrf

            {{-- ================= NAMA ================= --}}
            <div class="mb-3">
                <label class="form-label">Nama Tarif</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    placeholder="Contoh: Reguler, Eksekutif" value="{{ old('name') }}" required>

                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ================= BASE PRICE ================= --}}
            <div class="mb-3">
                <label class="form-label">Harga Dasar</label>
                <input type="number" name="base_price" class="form-control @error('base_price') is-invalid @enderror"
                    value="{{ old('base_price') }}" min="0" placeholder="Contoh: 20000" required>

                <small class="text-muted">Biaya awal sebelum dihitung jarak</small>

                @error('base_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ================= PRICE PER KM ================= --}}
            <div class="mb-3">
                <label class="form-label">Harga per KM</label>
                <input type="number" name="price_per_km" class="form-control @error('price_per_km') is-invalid @enderror"
                    value="{{ old('price_per_km') }}" min="0" placeholder="Contoh: 3000" required>

                <small class="text-muted">Dikalikan dengan jarak perjalanan</small>

                @error('price_per_km')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ================= PICKUP FEE ================= --}}
            <div class="mb-3">
                <label class="form-label">Biaya Jemput (Pickup Fee)</label>
                <input type="number" name="pickup_fee" class="form-control @error('pickup_fee') is-invalid @enderror"
                    value="{{ old('pickup_fee') }}" min="0" placeholder="Contoh: 10000" required>

                <small class="text-muted">Biaya tambahan jika dijemput ke lokasi</small>

                @error('pickup_fee')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ================= MIN PRICE ================= --}}
            <div class="mb-3">
                <label class="form-label">Harga Minimum (Opsional)</label>
                <input type="number" name="min_price" class="form-control @error('min_price') is-invalid @enderror"
                    value="{{ old('min_price') }}" min="0" placeholder="Contoh: 50000">

                <small class="text-muted">Batas harga paling rendah</small>

                @error('min_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ================= MAX PRICE ================= --}}
            <div class="mb-3">
                <label class="form-label">Harga Maksimum (Opsional)</label>
                <input type="number" name="max_price" class="form-control @error('max_price') is-invalid @enderror"
                    value="{{ old('max_price') }}" min="0" placeholder="Contoh: 200000">

                <small class="text-muted">Batas harga paling tinggi</small>

                @error('max_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ================= BUTTON ================= --}}
            <div class="d-flex gap-2">
                <button class="btn btn-success">
                    💾 Simpan Tarif
                </button>

                <a href="{{ route('tariffs.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </div>

        </form>
    </div>
@endsection
