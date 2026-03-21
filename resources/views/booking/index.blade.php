@extends('layouts.app')

@section('content')

<h3>Pilih Jadwal</h3>

<table class="table">
    @foreach($schedules as $s)
    <tr>
        <td>{{ $s->origin->name }} → {{ $s->destination->name }}</td>
        <td>{{ $s->departure_time }}</td>
        <td>
            <a href="{{ route('booking.create', $s->id) }}" class="btn btn-primary">
                Pesan
            </a>
        </td>
    </tr>
    @endforeach
</table>

@endsection