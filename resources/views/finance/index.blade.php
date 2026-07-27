@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">💰 Dashboard Keuangan</h4>
                <small class="text-muted">Ringkasan pemasukan & pengeluaran sistem</small>
            </div>
        </div>

        {{-- ================= SUMMARY ================= --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0">
                    <small class="text-muted">Pemasukan</small>
                    <h4 class="fw-bold text-success mb-0">
                        Rp {{ number_format($income ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0">
                    <small class="text-muted">Pengeluaran</small>
                    <h4 class="fw-bold text-danger mb-0">
                        Rp {{ number_format($expense ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0">
                    <small class="text-muted">Saldo</small>
                    <h4 class="fw-bold text-primary mb-0">
                        Rp {{ number_format($balance ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div>

        </div>

        {{-- ================= ACTION ================= --}}
        <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">

            <a href="{{ route('finance.expense.create') }}" class="btn btn-primary btn-premium">
                ➕ Tambah Pengeluaran
            </a>

            {{-- <a href="{{ route('finance.setoran') }}" class="btn btn-success btn-premium">
                💸 Setoran Driver
            </a> --}}

        </div>

        {{-- ================= CHART ================= --}}
        <div class="card card-premium p-3 border-0 mb-3">
            <h6 class="text-muted mb-3">📊 Grafik Pemasukan (7 Hari Terakhir)</h6>
            <canvas id="incomeChart"></canvas>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="card card-premium border-0">

            <div class="card-body">

                <h6 class="mb-3">📄 Data Pengeluaran</h6>

                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">

                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th class="text-start">Judul</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Tanggal</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($expenses as $i => $e)
                                <tr>

                                    {{-- NOMOR (FIX PAGINATION) --}}
                                    <td class="text-center">
                                        {{ $expenses->firstItem() + $i }}
                                    </td>

                                    {{-- JUDUL --}}
                                    <td>
                                        <div class="fw-semibold">{{ $e->title }}</div>
                                    </td>

                                    {{-- KATEGORI --}}
                                    <td class="text-center">
                                        {{ $e->category }}
                                    </td>

                                    {{-- JUMLAH --}}
                                    <td class="text-danger fw-semibold text-center">
                                        Rp {{ number_format($e->amount ?? 0, 0, ',', '.') }}
                                    </td>

                                    {{-- TANGGAL --}}
                                    <td class="text-center">
                                        {{ $e->expense_date ? \Carbon\Carbon::parse($e->expense_date)->format('d M Y') : '-' }}
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">

                                            <a href="{{ route('finance.expense.edit', $e->id) }}"
                                                class="btn btn-warning btn-sm">
                                                ✏️
                                            </a>

                                            <form action="{{ route('finance.expense.delete', $e->id) }}" method="POST"
                                                onsubmit="return confirmDelete(this)">
                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm btn-delete">
                                                    🗑️
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            📭 Belum ada data pengeluaran
                                        </div>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>

                {{-- ================= PAGINATION ================= --}}
                @if ($expenses->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div class="text-muted small">
                            Menampilkan {{ $expenses->firstItem() }} - {{ $expenses->lastItem() }}
                            dari {{ $expenses->total() }} data
                        </div>

                        <div>
                            {{ $expenses->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                @endif

            </div>

        </div>

    </div>
@endsection


@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const chartData = @json($chartData ?? []);

            const labels = chartData.length ?
                chartData.map(item =>
                    new Date(item.date).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short'
                    })
                ) :
                ['-'];

            const data = chartData.length ?
                chartData.map(item => item.total) :
                [0];

            new Chart(document.getElementById('incomeChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pemasukan',
                        data: data,
                        tension: 0.4,
                        fill: true
                    }]
                }
            });

        });

        function confirmDelete(form) {
            if (!confirm('Hapus pengeluaran ini?')) return false;

            const btn = form.querySelector('.btn-delete');
            if (btn) {
                btn.disabled = true;
                btn.innerText = '...';
            }

            return true;
        }
    </script>
@endpush
