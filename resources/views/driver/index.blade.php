@extends('layouts.app')

@section('content')

<h3>📋 Booking Masuk</h3>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>ID</th>
            <th>Penumpang</th>
            <th>Rute</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($bookings as $b)
            <tr>
                <td>{{ $b->id }}</td>
                <td>{{ $b->user->name }}</td>
                <td>
                    {{ $b->schedule->origin->name }} →
                    {{ $b->schedule->destination->name }}
                </td>
                <td>
                    <span class="badge bg-warning">{{ $b->status }}</span>
                </td>
                <td>
                    <a href="{{ route('driver.show', $b->id) }}"
                       class="btn btn-info btn-sm">
                        Detail
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">
                    Tidak ada booking
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection