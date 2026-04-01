@extends('layouts.app')

@section('content')

<h3>Dashboard Super Admin</h3>

<div class="row mt-3">

    <div class="col-md-3">
        <div class="card p-3">
            <h6>Total Booking</h6>
            <h3>{{ $totalBooking }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h6>Total Trip</h6>
            <h3>{{ $totalTrip }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h6>Pemasukan</h6>
            <h3>Rp {{ number_format($income) }}</h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h6>Belum Disetor</h6>
            <h3>Rp {{ number_format($pendingIncome) }}</h3>
        </div>
    </div>

</div>

@endsection