@extends('layouts.app')

@section('content')

<h3 class="mb-4">➕ Tambah Tarif</h3>

<form action="{{ route('tariffs.store') }}" method="POST">
    @csrf

    {{-- NAMA --}}
    <div class="mb-3">
        <label>Nama Tarif</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    {{-- BASE PRICE --}}
    <div class="mb-3">
        <label>Harga Dasar (Meeting Point)</label>
        <input type="number" name="base_price" class="form-control" required>
    </div>

    {{-- PRICE PER KM --}}
    <div class="mb-3">
        <label>Harga per KM</label>
        <input type="number" name="price_per_km" class="form-control" required>
    </div>

    {{-- PICKUP FEE --}}
    <div class="mb-3">
        <label>Biaya Jemput (Pickup Fee)</label>
        <input type="number" name="pickup_fee" class="form-control" required>
    </div>

    {{-- MIN PRICE --}}
    <div class="mb-3">
        <label>Harga Minimum (Optional)</label>
        <input type="number" name="min_price" class="form-control">
    </div>

    {{-- MAX PRICE --}}
    <div class="mb-3">
        <label>Harga Maksimum (Optional)</label>
        <input type="number" name="max_price" class="form-control">
    </div>

    <button class="btn btn-success">Simpan</button>
</form>

@endsection