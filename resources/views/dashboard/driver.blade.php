@extends('layouts.app')

@section('content')

<h3>Dashboard Driver</h3>

<div class="row mt-3">

    <div class="col-md-6">
        <div class="card p-3">
            <h6>Total Trip</h6>
            <h3>{{ $totalTrip }}</h3>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3">
            <h6>Trip Selesai</h6>
            <h3>{{ $completedTrip }}</h3>
        </div>
    </div>

</div>

@endsection