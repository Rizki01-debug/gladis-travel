@extends('layouts.app')

@section('content')

<h3>Tambah Meeting Point</h3>

<form method="POST" action="{{ route('meeting-points.store') }}">
    @csrf

    <select name="city_id" class="form-control mb-3">
        @foreach($cities as $city)
            <option value="{{ $city->id }}">{{ $city->name }}</option>
        @endforeach
    </select>

    <input type="text" name="name" class="form-control mb-2" placeholder="Nama titik">
    <input type="text" name="address" class="form-control mb-2" placeholder="Alamat">
    <input type="text" name="google_maps_link" class="form-control mb-2" placeholder="Link Google Maps">

    <button class="btn btn-success">Simpan</button>

</form>

@endsection