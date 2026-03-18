@extends('layouts.app')

@section('content')

<h3>Data Kendaraan</h3>

<a href="{{ route('vehicles.create') }}" class="btn btn-primary mb-3">Tambah Kendaraan</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Plat Nomor</th>
            <th>Kapasitas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vehicles as $v)
        <tr>
            <td>{{ $v->name }}</td>
            <td>{{ $v->plate_number }}</td>
            <td>{{ $v->seat_capacity }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection