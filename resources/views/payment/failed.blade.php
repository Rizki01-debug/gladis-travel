@extends('layouts.app')

@section('title', 'Pembayaran Gagal')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="display-1 text-danger mb-4">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h2 class="mb-3">Pembayaran Gagal</h2>
                    <p class="text-muted">
                        Maaf, pembayaran Anda gagal diproses. Silakan coba lagi.
                    </p>
                    @if($payment)
                        <div class="alert alert-danger mt-3">
                            <strong>Order ID:</strong> {{ $payment->order_id }}<br>
                            <strong>Status:</strong> <span class="badge bg-danger">{{ $payment->status_label }}</span>
                        </div>
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('booking.my') }}" class="btn btn-primary">
                            <i class="fas fa-list"></i> Lihat Booking Saya
                        </a>
                        <a href="{{ route('landing') }}" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection