@extends('layouts.app')

@section('content')

<h3>Jadwal Keberangkatan</h3>

<a href="{{ route('schedules.create') }}" class="btn btn-primary mb-3">Tambah Jadwal</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Rute</th>
            <th>Kendaraan</th>
            <th>Jam</th>
        </tr>
    </thead>
    <tbody>
        @foreach($schedules as $s)
        <tr>
            <td>{{ $s->origin->name }} → {{ $s->destination->name }}</td>
            <td>{{ $s->vehicle->name }}</td>
            <td>{{ $s->departure_time }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection