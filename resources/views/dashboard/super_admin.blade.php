@extends('layouts.app')

@section('content')

<h4 class="mb-4 fw-semibold">👑 Dashboard Super Admin</h4>

{{-- ================= SUMMARY ================= --}}
<div class="row g-3">

    {{-- BOOKING --}}
    <div class="col-md-4">
        <div class="card card-premium p-3">
            <small class="text-muted">Total Booking</small>
            <h4 class="fw-bold">{{ $totalBooking ?? 0 }}</h4>
        </div>
    </div>

    {{-- TRIP --}}
    <div class="col-md-4">
        <div class="card card-premium p-3">
            <small class="text-muted">Total Trip</small>
            <h4 class="fw-bold">{{ $totalTrip ?? 0 }}</h4>
        </div>
    </div>

    {{-- PEMASUKAN --}}
    <div class="col-md-4">
        <div class="card card-premium p-3">
            <small class="text-muted">Total Pemasukan</small>
            <h4 class="fw-bold text-success">
                Rp {{ number_format($income ?? 0, 0, ',', '.') }}
            </h4>
        </div>
    </div>

    {{-- BELUM DISETOR --}}
    {{-- <div class="col-md-3">
        <div class="card card-premium p-3">
            <small class="text-muted">Belum Disetor</small>
            <h4 class="fw-bold text-danger">
                Rp {{ number_format($pendingIncome ?? 0, 0, ',', '.') }}
            </h4>
        </div>
    </div> --}}

</div>

{{-- ================= CHART ================= --}}
<div class="row g-3 mt-1">

    {{-- BOOKING CHART --}}
    <div class="col-md-6">
        <div class="card card-premium p-3">
            <h6 class="mb-3 fw-semibold">📦 Booking (7 Hari)</h6>
            <canvas id="bookingChart"></canvas>
        </div>
    </div>

    {{-- REVENUE CHART --}}
    <div class="col-md-6">
        <div class="card card-premium p-3">
            <h6 class="mb-3 fw-semibold">💰 Revenue (7 Hari)</h6>
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

</div>

{{-- ================= INFO + ACTION ================= --}}
<div class="row g-3 mt-1">

    {{-- INFO --}}
    <div class="col-md-6">
        <div class="card card-premium p-3">
            <h6 class="mb-3 fw-semibold">📊 Ringkasan Sistem</h6>

            <ul class="mb-0 text-muted">
                <li>Total booking aktif & selesai</li>
                <li>Total trip berjalan</li>
                <li>Pemasukan dari transaksi</li>
                <li>Status setoran driver</li>
            </ul>
        </div>
    </div>

    {{-- ACTION --}}
    <div class="col-md-6">
        <div class="card card-premium p-3">
            <h6 class="mb-3 fw-semibold">⚡ Quick Action</h6>

            <div class="d-grid gap-2">

                <a href="{{ route('schedules.index') }}" class="btn btn-primary btn-premium">
                    ➕ Kelola Jadwal
                </a>

                <a href="{{ route('tariffs.index') }}" class="btn btn-warning btn-premium">
                    💸 Kelola Tarif
                </a>

                <a href="{{ route('finance.index') }}" class="btn btn-success btn-premium">
                    💰 Lihat Keuangan
                </a>

            </div>
        </div>
    </div>

</div>

@endsection


{{-- ================= SCRIPT CHART ================= --}}
@push('scripts')
<script>

const bookingLabels = @json($bookings->pluck('date') ?? []);
const bookingData = @json($bookings->pluck('total') ?? []);

const revenueLabels = @json($revenue->pluck('date') ?? []);
const revenueData = @json($revenue->pluck('total') ?? []);

// BOOKING
new Chart(document.getElementById('bookingChart'), {
    type: 'line',
    data: {
        labels: bookingLabels,
        datasets: [{
            data: bookingData,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

// REVENUE
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: revenueLabels,
        datasets: [{
            data: revenueData
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

</script>
@endpush