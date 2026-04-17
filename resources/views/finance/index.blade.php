@extends('layouts.app')

@section('content')
    <h3 class="mb-4">💰 Dashboard Keuangan</h3>

    {{-- ================= SUMMARY ================= --}}
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0">
                <h6 class="text-muted">Pemasukan</h6>
                <h3 class="text-success">
                    Rp {{ number_format($income, 0, ',', '.') }}
                </h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0">
                <h6 class="text-muted">Pengeluaran</h6>
                <h3 class="text-danger">
                    Rp {{ number_format($expense, 0, ',', '.') }}
                </h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0">
                <h6 class="text-muted">Saldo</h6>
                <h3 class="text-primary">
                    Rp {{ number_format($balance, 0, ',', '.') }}
                </h3>
            </div>
        </div>

    </div>

    {{-- ================= ACTION ================= --}}
    <div class="mb-3 d-flex justify-content-between">
        <a href="{{ route('finance.expense.create') }}" class="btn btn-primary">
            ➕ Tambah Pengeluaran
        </a>

        <a href="{{ route('finance.setoran') }}" class="btn btn-success">
            💸 Setoran Driver
        </a>
    </div>

    {{-- ================= GRAFIK ================= --}}
    <div class="card p-3 mb-4 shadow-sm border-0">
        <h5>📊 Grafik Pemasukan</h5>
        <canvas id="incomeChart"></canvas>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <h5 class="mb-3">📄 Data Pengeluaran</h5>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Tanggal</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($expenses as $i => $e)
                            <tr>
                                <td>{{ $i + 1 }}</td>

                                <td><strong>{{ $e->title }}</strong></td>

                                <td>{{ $e->category }}</td>

                                <td class="text-danger">
                                    Rp {{ number_format($e->amount, 0, ',', '.') }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($e->expense_date)->format('d M Y') }}
                                </td>

                                <td>
                                    {{-- EDIT --}}
                                    <a href="{{ route('finance.expense.edit', $e->id) }}" class="btn btn-warning btn-sm">
                                        ✏️
                                    </a>

                                    {{-- DELETE --}}
                                    <form action="{{ route('finance.expense.delete', $e->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus pengeluaran ini?')">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Belum ada data pengeluaran
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            {{-- ================= PAGINATION ================= --}}
            <div class="mt-3">
                {{ $expenses->links() }}
            </div>

        </div>
    </div>
@endsection


{{-- ================= SCRIPT ================= --}}
@section('scripts')
    <script>
        const chartData = @json($chartData ?? []);

        const labels = chartData.length ?
            chartData.map(item => item.date) :
            ['Belum ada data'];

        const data = chartData.length ?
            chartData.map(item => item.total) :
            [0];

        const ctx = document.getElementById('incomeChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pemasukan',
                    data: data,
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
    </script>
@endsection
