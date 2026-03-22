@extends('layouts.app')

@section('content')

<h3>Laporan Keuangan</h3>

<form method="GET" class="mb-3">
    <div class="row">
        <div class="col">
            <input type="date" name="start_date" class="form-control" value="{{ $start }}">
        </div>
        <div class="col">
            <input type="date" name="end_date" class="form-control" value="{{ $end }}">
        </div>
        <div class="col">
            <button class="btn btn-primary">Filter</button>
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
        @foreach($bookings as $b)
        <tr>
            <td>#{{ $b->id }}</td>
            <td>Rp {{ number_format($b->price_estimation) }}</td>
            <td>{{ $b->created_at }}</td>
        </tr>
        @endforeach
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
        @foreach($expenses as $e)
        <tr>
            <td>{{ $e->title }}</td>
            <td>{{ $e->category }}</td>
            <td>Rp {{ number_format($e->amount) }}</td>
            <td>{{ $e->expense_date }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<hr>

<h4>Total Pemasukan: Rp {{ number_format($totalIncome) }}</h4>
<h4>Total Pengeluaran: Rp {{ number_format($totalExpense) }}</h4>
<h3>Saldo: Rp {{ number_format($balance) }}</h3>

@endsection