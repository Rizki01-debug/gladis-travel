@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">
        <h3 class="fw-bold">📊 Dashboard Admin</h3>
        <p class="text-muted mb-0">Ringkasan keuangan dan transaksi</p>
    </div>

    {{-- SUMMARY CARD --}}
    <div class="row g-3">

        {{-- TOTAL PEMASUKAN --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted">Total Pemasukan</h6>
                    <h3 class="fw-bold text-success">
                        Rp {{ number_format($income ?? 0) }}
                    </h3>
                </div>
            </div>
        </div>

        {{-- BELUM DISETOR --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted">Belum Disetor</h6>
                    <h3 class="fw-bold text-warning">
                        Rp {{ number_format($pendingIncome ?? 0) }}
                    </h3>
                </div>
            </div>
        </div>

        {{-- TOTAL TRANSAKSI --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted">Total Transaksi</h6>
                    <h3 class="fw-bold">
                        {{ number_format($totalTransaction ?? 0) }}
                    </h3>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= GRAFIK ================= --}}
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h5 class="mb-3">📈 Grafik Pemasukan</h5>
            <canvas id="incomeChart"></canvas>
        </div>
    </div>

</div>

@endsection


@section('scripts')
<script>
    const chartData = @json($chartData ?? []);

    const labels = chartData.map(item => item.date);
    const data = chartData.map(item => item.total);

    const ctx = document.getElementById('incomeChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pemasukan',
                data: data,
                borderWidth: 2,
                tension: 0.3
            }]
        }
    });
</script>
@endsection