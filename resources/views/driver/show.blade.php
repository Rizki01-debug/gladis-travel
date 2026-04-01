@extends('layouts.app')

@section('content')
    <h3>🚗 Detail Booking</h3>

    <div class="card shadow-sm p-4">

        <p><strong>Penumpang:</strong> {{ $booking->user->name }}</p>

        <p><strong>Rute:</strong>
            {{ $booking->schedule->origin->name }} →
            {{ $booking->schedule->destination->name }}
        </p>

        <p><strong>Kendaraan:</strong>
            {{ $booking->schedule->vehicle->name ?? '-' }}
        </p>

        <p><strong>Status:</strong>
            @if ($booking->status == 'pending')
                <span class="badge bg-warning text-dark">Pending</span>
            @elseif ($booking->status == 'confirmed')
                <span class="badge bg-primary">Dikonfirmasi</span>
            @elseif ($booking->status == 'completed')
                <span class="badge bg-success">Selesai</span>
            @elseif ($booking->status == 'rejected')
                <span class="badge bg-danger">Ditolak</span>
            @endif
        </p>

        {{-- ================= AKSI ================= --}}
        @if ($booking->status == 'pending')
            <div class="d-flex gap-2 mt-3">

                {{-- TERIMA --}}
                <form action="{{ route('driver.confirm', $booking->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        ✅ Terima
                    </button>
                </form>

                <form action="{{ route('driver.reject', $booking->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        ❌ Tolak
                    </button>
                </form>
            </div>
        @else
            <div class="alert alert-info mt-3">
                Booking sudah diproses.
            </div>
        @endif

    </div>
@endsection
