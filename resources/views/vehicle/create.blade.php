@extends('layouts.app')

@section('content')

<h3>Tambah Kendaraan</h3>

<form action="{{ route('vehicles.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label>Nama Kendaraan</label>
        <input type="text" name="name" class="form-control">
    </div>

    <div class="mb-3">
        <label>Plat Nomor</label>
        <input type="text" name="plate_number" class="form-control">
    </div>

    <div class="mb-3">
        <label>Kapasitas Kursi</label>
        <input type="number" name="seat_capacity" class="form-control">
    </div>

    <button class="btn btn-success">Simpan</button>
</form>

@endsection