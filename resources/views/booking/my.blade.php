@extends('layouts.app')

@section('content')

<h3>📋 Booking Saya</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Rute</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($bookings as $booking)
            <tr>
                <td>{{ $booking->id }}</td>
                <td>
                    {{ $booking->schedule->origin->name ?? '-' }}
                    →
                    {{ $booking->schedule->destination->name ?? '-' }}
                </td>
                <td>
                    <span class="badge bg-{{ 
                        $booking->status == 'pending' ? 'warning' :
                        ($booking->status == 'confirmed' ? 'info' :
                        ($booking->status == 'completed' ? 'success' : 'secondary'))
                    }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">Belum ada booking</td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection