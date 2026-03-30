@extends('layouts.app')

@section('content')

<h3>🚗 Detail Booking</h3>

<div class="card p-3">

    <p><b>Penumpang:</b> {{ $booking->user->name }}</p>

    <p><b>Rute:</b>
        {{ $booking->schedule->origin->name }} →
        {{ $booking->schedule->destination->name }}
    </p>

    <p><b>Kendaraan:</b> {{ $booking->schedule->vehicle->name }}</p>

    <p><b>Status:</b>
        <span class="badge bg-warning">{{ $booking->status }}</span>
    </p>

    {{-- ================= AKSI ================= --}}
    <div class="mt-3">

        {{-- TERIMA --}}
        <form action="{{ route('driver.confirm', $booking->id) }}" method="POST">
            @csrf
            <button class="btn btn-success">
                ✅ Terima Booking
            </button>
        </form>

        {{-- TOLAK --}}
        <form action="{{ route('driver.reject', $booking->id) }}" method="POST" class="mt-2">
            @csrf
            <button class="btn btn-danger">
                ❌ Tolak Booking
            </button>
        </form>

    </div>

</div>

@endsection