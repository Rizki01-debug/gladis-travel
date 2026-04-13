@extends('layouts.app')

@section('content')
    <h3>Dashboard Driver</h3>

    <div class="row">

        {{-- TOTAL TRIP --}}
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <b>Total Trip</b>
                <h4>{{ $totalTrip }}</h4>
            </div>
        </div>

        {{-- TRIP SELESAI --}}
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <b>Trip Selesai</b>
                <h4>{{ $completedTrip }}</h4>
            </div>
        </div>

        {{-- TOTAL EARNING --}}
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <b>Total Earnings</b>
                <h4 class="text-primary">Rp {{ number_format($totalEarning) }}</h4>
            </div>
        </div>

        {{-- BELUM SETOR --}}
        <div class="col-md-6 mb-3">
            <div class="card p-3">
                <b>Belum Disetor</b>
                <h4 class="text-warning">Rp {{ number_format($unpaidEarning) }}</h4>
            </div>
        </div>

        {{-- SUDAH SETOR --}}
        <div class="col-md-6 mb-3">
            <div class="card p-3">
                <b>Sudah Disetor</b>
                <h4 class="text-success">Rp {{ number_format($paidEarning) }}</h4>
            </div>
        </div>

    </div>
@endsection
