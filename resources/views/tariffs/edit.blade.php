@extends('layouts.app')

@section('content')

<h3 class="mb-4">✏ Edit Tarif</h3>

<form action="{{ route('tariffs.update', $tariff->id) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- NAMA --}}
    <div class="mb-3">
        <label>Nama Tarif</label>
        <input type="text" name="name" class="form-control"
               value="{{ $tariff->name }}" required>
    </div>

    {{-- BASE PRICE --}}
    <div class="mb-3">
        <label>Harga Dasar</label>
        <input type="number" name="base_price" class="form-control"
               value="{{ $tariff->base_price }}" required>
    </div>

    {{-- PRICE PER KM --}}
    <div class="mb-3">
        <label>Harga per KM</label>
        <input type="number" name="price_per_km" class="form-control"
               value="{{ $tariff->price_per_km }}" required>
    </div>

    {{-- PICKUP FEE --}}
    <div class="mb-3">
        <label>Pickup Fee</label>
        <input type="number" name="pickup_fee" class="form-control"
               value="{{ $tariff->pickup_fee }}" required>
    </div>

    {{-- MIN --}}
    <div class="mb-3">
        <label>Min Price</label>
        <input type="number" name="min_price" class="form-control"
               value="{{ $tariff->min_price }}">
    </div>

    {{-- MAX --}}
    <div class="mb-3">
        <label>Max Price</label>
        <input type="number" name="max_price" class="form-control"
               value="{{ $tariff->max_price }}">
    </div>

    <button class="btn btn-primary">Update</button>
</form>

@endsection