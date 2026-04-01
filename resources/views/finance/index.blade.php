@extends('layouts.app')

@section('content')

<h3 class="mb-4">💰 Dashboard Keuangan</h3>

<div class="row mb-4">

    <div class="col-md-4">
        <div class="card p-3 shadow-sm">
            <h6>Pemasukan</h6>
            <h3 class="text-success">Rp {{ number_format($income, 0, ',', '.') }}</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3 shadow-sm">
            <h6>Pengeluaran</h6>
            <h3 class="text-danger">Rp {{ number_format($expense, 0, ',', '.') }}</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3 shadow-sm">
            <h6>Saldo</h6>
            <h3 class="text-primary">Rp {{ number_format($balance, 0, ',', '.') }}</h3>
        </div>
    </div>

</div>

{{-- 🔥 FIX ROUTE --}}
<a href="{{ route('finance.expense.create') }}" class="btn btn-primary mb-3">
    + Tambah Pengeluaran
</a>

{{-- ================= GRAFIK ================= --}}
<div class="card p-3 mb-4 shadow-sm">
    <h5>📊 Grafik Pemasukan</h5>
    <canvas id="incomeChart"></canvas>
</div>

{{-- ================= TABLE ================= --}}
<div class="card p-3 shadow-sm">
    <h5 class="mb-3">📄 Data Pengeluaran</h5>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses as $e)
                <tr>
                    <td>{{ $e->title }}</td>
                    <td>{{ $e->category }}</td>
                    <td>Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
                    <td>{{ \Carbon\Carbon::parse($e->expense_date)->format('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@section('scripts')
<script>
    const chartData = @json($chartData ?? []);

    const labels = chartData.length
        ? chartData.map(item => item.date)
        : ['Belum ada data'];

    const data = chartData.length
        ? chartData.map(item => item.total)
        : [0];

    const ctx = document.getElementById('incomeChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pemasukan',
                data: data,
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        }
    });
</script>
@endsection