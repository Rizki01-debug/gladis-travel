@extends('layouts.app')

@section('title', 'Pembayaran Berhasil')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="display-1 text-success mb-4">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="mb-3">Pembayaran Berhasil!</h2>
                    <p class="text-muted">
                        Terima kasih telah melakukan pembayaran. Booking Anda akan segera diproses.
                    </p>
                    @if($payment)
                        <div class="alert alert-success mt-3">
                            <strong>Order ID:</strong> {{ $payment->order_id }}<br>
                            <strong>Total:</strong> {{ $payment->formatted_amount }}<br>
                            <strong>Status:</strong> <span class="badge bg-success">{{ $payment->status_label }}</span>
                        </div>
                    @endif
                    <a href="{{ route('booking.my') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-list"></i> Lihat Booking Saya
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection