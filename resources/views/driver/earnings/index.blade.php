@extends('layouts.app')

@section('content')

<h3>💰 Penghasilan Driver</h3>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card p-3">
            <b>Total</b>
            <h5>Rp {{ number_format($total) }}</h5>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <b>Sudah Disetor</b>
            <h5 class="text-success">Rp {{ number_format($paid) }}</h5>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <b>Belum Disetor</b>
            <h5 class="text-danger">Rp {{ number_format($unpaid) }}</h5>
        </div>
    </div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID Booking</th>
            <th>Nominal</th>
            <th>Status</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($earnings as $e)
        <tr>
            <td>#{{ $e->booking_id }}</td>
            <td>Rp {{ number_format($e->amount) }}</td>
            <td>
                @if($e->status == 'paid')
                    <span class="badge bg-success">Paid</span>
                @else
                    <span class="badge bg-danger">Unpaid</span>
                @endif
            </td>
            <td>{{ $e->created_at->format('d M Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection