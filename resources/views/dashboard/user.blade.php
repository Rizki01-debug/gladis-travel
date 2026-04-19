@extends('layouts.app')

@section('content')
<div class="container-fluid fade-in">

    <h4 class="fw-bold mb-4">👋 Dashboard</h4>

    {{-- ================= STATS ================= --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h6>Total Booking</h6>
                <h3 class="fw-bold">{{ $total }}</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h6>Pending</h6>
                <h3 class="fw-bold text-warning">{{ $pending }}</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h6>Selesai</h6>
                <h3 class="fw-bold text-success">{{ $completed }}</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h6>Cancel</h6>
                <h3 class="fw-bold text-danger">{{ $cancelled }}</h3>
            </div>
        </div>

    </div>

    {{-- ================= QUICK ACTION ================= --}}
    <div class="mb-4">
        <a href="{{ route('booking.index') }}" class="btn btn-primary">
            🚀 Booking Sekarang
        </a>

        <a href="{{ route('booking.my') }}" class="btn btn-outline-secondary">
            📋 Lihat Booking Saya
        </a>
    </div>

    {{-- ================= LAST BOOKING ================= --}}
    <div class="card">
        <div class="card-body">

            <h5 class="mb-3">Booking Terakhir</h5>

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Rute</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse ($latestBookings as $b)
                        <tr>
                            <td>
                                {{ $b->schedule->origin->name ?? '-' }}
                                →
                                {{ $b->schedule->destination->name ?? '-' }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($b->departure_date)->format('d M Y') }}
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst($b->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                Belum ada booking
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection