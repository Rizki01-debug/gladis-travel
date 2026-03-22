@extends('layouts.app')

@section('content')

<h3>Detail Perjalanan</h3>

<p>
    {{ $schedule->origin->name }} → {{ $schedule->destination->name }}
</p>

<table class="table">
    <thead>
        <tr>
            <th>Nama Penumpang</th>
            <th>Pickup</th>
            <th>Lokasi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($schedule->bookings as $booking)
        <tr>
            <td>{{ $booking->user->name }}</td>
            <td>{{ $booking->pickup_type }}</td>
            <td>
                @if($booking->pickup_maps)
                    <a href="https://www.google.com/maps?q={{ $booking->pickup_maps }}" target="_blank">
                        Lihat Map
                    </a>
                @else
                    Meeting Point
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection