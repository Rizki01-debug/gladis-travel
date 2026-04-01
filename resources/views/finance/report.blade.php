@extends('layouts.app')

@section('content')
<h3>Laporan Keuangan</h3>

<form method="GET" class="mb-3">
    <div class="row g-2">

        <div class="col-md-3">
            <input type="date" name="start_date" class="form-control"
                value="{{ $start ?? '' }}">
        </div>

        <div class="col-md-3">
            <input type="date" name="end_date" class="form-control"
                value="{{ $end ?? '' }}">
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>

        <div class="col-md-2">
            <a href="{{ route('finance.export.pdf', [
                'start_date' => request('start_date'),
                'end_date' => request('end_date')
            ]) }}"
                class="btn btn-danger w-100">
                Download PDF
            </a>
        </div>

    </div>
</form>

<hr>

<h5>Pemasukan</h5>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID Booking</th>
            <th>Jumlah</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transactions as $t)
            <tr>
                <td>#{{ $t->booking_id }}</td>
                <td>Rp {{ number_format($t->amount) }}</td>
                <td>{{ $t->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center text-muted">
                    Tidak ada data pemasukan
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<h5>Pengeluaran</h5>

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
                <td>Rp {{ number_format($e->amount) }}</td>
                <td>{{ $e->expense_date }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">
                    Tidak ada data pengeluaran
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<hr>

<h4>Total Pemasukan: Rp {{ number_format($totalIncome) }}</h4>
<h4>Total Pengeluaran: Rp {{ number_format($totalExpense) }}</h4>
<h3>Saldo: Rp {{ number_format($balance) }}</h3>

@endsection