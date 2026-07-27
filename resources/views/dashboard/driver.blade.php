@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">🚐 Dashboard Driver</h4>
                <small class="text-muted">Ringkasan aktivitas dan penghasilan kamu</small>
            </div>
        </div>

        {{-- ================= STATS ================= --}}
        <div class="row g-3">

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0 h-100">
                    <small class="text-muted">Total Trip</small>
                    <h3 class="fw-bold mb-0">{{ $totalTrip ?? 0 }}</h3>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0 h-100">
                    <small class="text-muted">Trip Selesai</small>
                    <h3 class="fw-bold text-success mb-0">{{ $completedTrip ?? 0 }}</h3>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0 h-100">
                    <small class="text-muted">Total Earnings</small>
                    <h3 class="fw-bold text-primary mb-0">
                        Rp {{ number_format($totalEarning ?? 0, 0, ',', '.') }}
                    </h3>
                </div>
            </div>

        </div>

        {{-- ================= BREAKDOWN ================= --}}
        <div class="row g-3 mt-1">

            {{-- <div class="col-md-6">
                <div class="card card-premium p-3 border-0 h-100">
                    <small class="text-muted">Belum Disetor</small>
                    <h4 class="fw-bold text-warning">
                        Rp {{ number_format($unpaidEarning ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div> --}}

            {{-- <div class="col-md-6">
                <div class="card card-premium p-3 border-0 h-100">
                    <small class="text-muted">Sudah Disetor</small>
                    <h4 class="fw-bold text-success">
                        Rp {{ number_format($paidEarning ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div> --}}

        </div>

        {{-- ================= CHART ================= --}}
        <div class="row mt-3 g-3">

            {{-- TRIP CHART --}}
            <div class="col-md-6">
                <div class="card card-premium border-0">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">📈 Trip 7 Hari Terakhir</h6>
                        <canvas id="tripChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- EARNING CHART --}}
            <div class="col-md-6">
                <div class="card card-premium border-0">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">💰 Earnings 7 Hari Terakhir</h6>
                        <canvas id="earningChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= QUICK ACTION ================= --}}
        <div class="row mt-3 g-3">

            <div class="col-md-6">
                <div class="card card-premium p-3 border-0 h-100">
                    <h6 class="text-muted mb-3">⚡ Quick Action</h6>

                    <a href="{{ route('driver.index') }}" class="btn btn-primary btn-premium w-100 mb-2">
                        📥 Booking Masuk
                    </a>

                    <a href="{{ route('driver.trips') }}" class="btn btn-success btn-premium w-100">
                        🚗 Kelola Trip
                    </a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-premium p-3 border-0 h-100">
                    <h6 class="text-muted mb-3">📊 Info</h6>

                    <ul class="mb-0 small text-muted">
                        <li>Total perjalanan yang kamu ambil</li>
                        <li>Trip yang sudah selesai</li>
                        <li>Total pemasukan</li>
                        <li>Status setoran</li>
                    </ul>
                </div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // ================= DATA =================
            const tripData = @json($tripChart ?? []);
            const earningData = @json($earningChart ?? []);

            const tripLabels = tripData.map(i => i.date);
            const tripValues = tripData.map(i => i.total);

            const earningLabels = earningData.map(i => i.date);
            const earningValues = earningData.map(i => i.total);

            // ================= TRIP CHART =================
            new Chart(document.getElementById('tripChart'), {
                type: 'line',
                data: {
                    labels: tripLabels,
                    datasets: [{
                        label: 'Trip',
                        data: tripValues,
                        tension: 0.4,
                        fill: true
                    }]
                }
            });

            // ================= EARNING CHART =================
            new Chart(document.getElementById('earningChart'), {
                type: 'bar',
                data: {
                    labels: earningLabels,
                    datasets: [{
                        label: 'Earnings',
                        data: earningValues
                    }]
                }
            });

        });
    </script>
@endpush
