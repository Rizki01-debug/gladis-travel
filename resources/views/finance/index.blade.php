@extends('layouts.app')

@section('content')

<h3>Dashboard Keuangan</h3>

<div class="row mb-4">

    <div class="col-md-4">
        <div class="card p-3">
            <h5>Pemasukan</h5>
            <h3>Rp {{ number_format($income) }}</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h5>Pengeluaran</h5>
            <h3>Rp {{ number_format($expense) }}</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h5>Saldo</h5>
            <h3>Rp {{ number_format($balance) }}</h3>
        </div>
    </div>

</div>

<a href="{{ route('expense.create') }}" class="btn btn-primary mb-3">
    + Tambah Pengeluaran
</a>

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

@endsection