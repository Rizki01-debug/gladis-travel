@extends('layouts.app')

@section('content')

<h3>🚗 Trip Saya</h3>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Status</th>
            <th>Mulai</th>
            <th>Selesai</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($trips as $trip)
            <tr>
                <td>{{ $trip->id }}</td>
                <td>{{ $trip->trip_status }}</td>
                <td>{{ $trip->start_time }}</td>
                <td>{{ $trip->end_time ?? '-' }}</td>
                <td>
                    @if ($trip->trip_status == 'ongoing')
                        <form action="{{ route('driver.trip.complete', $trip->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-success btn-sm">Selesai</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Belum ada trip</td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection