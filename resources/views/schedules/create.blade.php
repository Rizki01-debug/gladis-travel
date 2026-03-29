@extends('layouts.app')

@section('content')

<h3>Tambah Jadwal</h3>

<form method="POST" action="{{ route('schedules.store') }}">
    @csrf

    <select name="origin_city_id" class="form-control mb-2">
        <option>Pilih Kota Asal</option>
        @foreach($cities as $city)
            <option value="{{ $city->id }}">{{ $city->name }}</option>
        @endforeach
    </select>

    <select name="destination_city_id" class="form-control mb-2">
        <option>Pilih Kota Tujuan</option>
        @foreach($cities as $city)
            <option value="{{ $city->id }}">{{ $city->name }}</option>
        @endforeach
    </select>

    <select name="vehicle_id" class="form-control mb-2">
        <option>Pilih Kendaraan</option>
        @foreach($vehicles as $vehicle)
            <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
        @endforeach
    </select>

    <input type="time" name="departure_time" class="form-control mb-2">

    <button class="btn btn-success">Simpan</button>

</form>

@endsection