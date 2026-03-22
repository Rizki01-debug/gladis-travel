@extends('layouts.app')

@section('content')

<h3>Jadwal Saya</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Rute</th>
            <th>Jam</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($schedules as $s)
        <tr>
            <td>{{ $s->origin->name }} → {{ $s->destination->name }}</td>
            <td>{{ $s->departure_time }}</td>
            <td>
                <a href="{{ route('driver.show', $s->id) }}" class="btn btn-primary">
                    Lihat Penumpang
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection