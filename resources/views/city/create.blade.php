@extends('layouts.app')

@section('content')

<h3>Tambah Kota</h3>

<form method="POST" action="{{ route('cities.store') }}">
    @csrf

    <input type="text" name="name" class="form-control mb-3" placeholder="Nama Kota">

    <button class="btn btn-success">Simpan</button>

</form>

@endsection