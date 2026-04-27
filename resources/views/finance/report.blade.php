@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">📊 Laporan Keuangan</h4>
                <small class="text-muted">Analisis pemasukan & pengeluaran</small>
            </div>
        </div>

        {{-- ================= FILTER ================= --}}
        <div class="card card-premium p-3 border-0 mb-3">
            <form method="GET">
                <div class="row g-2 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label small">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary btn-premium w-100">
                            🔍 Filter
                        </button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('finance.export.pdf', request()->all()) }}"
                            class="btn btn-danger btn-premium w-100">
                            📄 PDF
                        </a>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('finance.report') }}" class="btn btn-secondary w-100">
                            🔄 Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>

        {{-- ================= SUMMARY ================= --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0">
                    <small class="text-muted">Total Pemasukan</small>
                    <h4 class="fw-bold text-success">
                        Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0">
                    <small class="text-muted">Total Pengeluaran</small>
                    <h4 class="fw-bold text-danger">
                        Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0">
                    <small class="text-muted">Saldo</small>
                    <h4 class="fw-bold text-primary">
                        Rp {{ number_format($balance ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div>

        </div>

        {{-- ================= PEMASUKAN ================= --}}
        <div class="card card-premium border-0 mb-3">
            <div class="card-body">

                <h6 class="mb-3">📥 Data Pemasukan</h6>

                <div class="table-responsive">
                    <table class="table table-premium align-middle">

                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th>ID Booking</th>
                                <th>Jumlah</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($transactions as $t)
                                <tr>
                                    <td class="text-center">
                                        {{ $transactions->firstItem() + $loop->index }}
                                    </td>

                                    <td class="text-center fw-semibold">
                                        #{{ $t->booking_id }}
                                    </td>

                                    <td class="text-success fw-semibold text-center">
                                        Rp {{ number_format($t->amount ?? 0, 0, ',', '.') }}
                                    </td>

                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($t->created_at)->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            📭 Tidak ada pemasukan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- PAGINATION PEMASUKAN --}}
                @if ($transactions->hasPages())
                    <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div class="text-muted small">
                            Menampilkan {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }}
                            dari {{ $transactions->total() }} data
                        </div>

                        <div>
                            {{ $transactions->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                @endif

            </div>
        </div>

        {{-- ================= PENGELUARAN ================= --}}
        <div class="card card-premium border-0">
            <div class="card-body">

                <h6 class="mb-3">📤 Data Pengeluaran</h6>

                <div class="table-responsive">
                    <table class="table table-premium align-middle">

                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th class="text-start">Judul</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($expenses as $e)
                                <tr>
                                    <td class="text-center">
                                        {{ $expenses->firstItem() + $loop->index }}
                                    </td>

                                    <td class="fw-semibold">
                                        {{ $e->title }}
                                    </td>

                                    <td class="text-center">
                                        {{ $e->category }}
                                    </td>

                                    <td class="text-danger fw-semibold text-center">
                                        Rp {{ number_format($e->amount ?? 0, 0, ',', '.') }}
                                    </td>

                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($e->expense_date)->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            📭 Tidak ada pengeluaran
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- PAGINATION PENGELUARAN --}}
                @if ($expenses->hasPages())
                    <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">

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
