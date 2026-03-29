@extends('layouts.app')

@section('content')

<h3>Pengaturan Sistem</h3>

<form action="{{ route('theme.update') }}" method="POST">
    @csrf

    <label>Warna Tema</label>

    <input type="color"
           name="theme_color"
           class="form-control mb-3"
           value="{{ auth()->user()->theme_color }}">

    <button class="btn btn-primary">Simpan Tema</button>

</form>

@endsection