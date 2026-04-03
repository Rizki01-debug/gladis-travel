@extends('layouts.app')

@section('content')
    <h3 class="mb-4">👑 Dashboard Super Admin</h3>

    <div class="row">

        {{-- TOTAL BOOKING --}}
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm p-3 border-0">
                <h6 class="text-muted">Total Booking</h6>
                <h3 class="fw-bold">{{ $totalBooking ?? 0 }}</h3>
            </div>
        </div>

        {{-- TOTAL TRIP --}}
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm p-3 border-0">
                <h6 class="text-muted">Total Trip</h6>
                <h3 class="fw-bold">{{ $totalTrip ?? 0 }}</h3>
            </div>
        </div>

        {{-- PEMASUKAN --}}
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm p-3 border-0">
                <h6 class="text-muted">Total Pemasukan</h6>
                <h3 class="fw-bold text-success">
                    Rp {{ number_format($income ?? 0) }}
                </h3>
            </div>
        </div>

        {{-- BELUM DISETOR --}}
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm p-3 border-0">
                <h6 class="text-muted">Belum Disetor</h6>
                <h3 class="fw-bold text-danger">
                    Rp {{ number_format($pendingIncome ?? 0) }}
                </h3>
            </div>
        </div>

    </div>

    {{-- ================= OPTIONAL: QUICK INFO ================= --}}
    <div class="row mt-4">

        <div class="col-md-6">
            <div class="card shadow-sm p-3 border-0">
                <h6 class="text-muted mb-2">📊 Ringkasan Sistem</h6>
                <ul class="mb-0">
                    <li>Total Booking aktif & selesai</li>
                    <li>Total Trip berjalan</li>
                    <li>Pemasukan dari transaksi</li>
                    <li>Status setoran driver</li>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm p-3 border-0">
                <h6 class="text-muted mb-2">⚡ Quick Action</h6>

                <a href="{{ route('schedules.index') }}" class="btn btn-sm btn-primary mb-2 w-100">
                    ➕ Kelola Jadwal
                </a>

                <a href="{{ route('tariffs.index') }}" class="btn btn-sm btn-warning mb-2 w-100">
                    💸 Kelola Tarif
                </a>

                <a href="{{ route('finance.index') }}" class="btn btn-sm btn-success w-100">
                    💰 Lihat Keuangan
                </a>
            </div>
        </div>

    </div>
@endsection
