@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran - GLADIS Travel')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('landing') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('booking.my') }}">Booking Saya</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Konfirmasi Pembayaran</li>
                </ol>
            </nav>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Konfirmasi Pembayaran
                    </h4>
                </div>
                
                <div class="card-body py-4">
                    <!-- Informasi Booking -->
                    <div class="alert alert-info">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <strong>Booking #{{ $booking->id }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ $booking->departure_date?->format('d M Y') ?? '-' }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-route me-1"></i>
                                    @if($booking->schedule && $booking->schedule->origin && $booking->schedule->destination)
                                        {{ $booking->schedule->origin->name }} → {{ $booking->schedule->destination->name }}
                                    @else
                                        -
                                    @endif
                                </small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted">Total Pembayaran</small>
                                <h3 class="text-primary mb-0 fw-bold">
                                    Rp {{ number_format($booking->price_estimation, 0, ',', '.') }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Form Pembayaran -->
                    <form id="payment-form" action="{{ route('payment.snap', $booking->id) }}" method="POST">
                        @csrf

                        <div class="text-center mb-4">
                            <div class="payment-icon mb-3">
                                <i class="fas fa-wallet text-primary" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="mb-2">Siap Melakukan Pembayaran?</h5>
                            <p class="text-muted">
                                Anda akan diarahkan ke halaman pembayaran Midtrans
                                <br>
                                <small>Pilih metode pembayaran yang tersedia (Kartu Kredit, Transfer Bank, QRIS, E-Wallet)</small>
                            </p>
                        </div>

                        <!-- Tombol -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="pay-button">
                                <i class="fas fa-lock me-2"></i>
                                Bayar Sekarang
                                <span class="badge bg-light text-dark ms-2">
                                    Rp {{ number_format($booking->price_estimation, 0, ',', '.') }}
                                </span>
                            </button>
                            <a href="{{ route('booking.my') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Kembali ke Booking Saya
                            </a>
                        </div>
                    </form>

                    <!-- Footer Info -->
                    <div class="text-center text-muted small mt-4">
                        <div class="d-flex justify-content-center gap-4">
                            <span>
                                <i class="fas fa-shield-alt me-1"></i>
                                Pembayaran Aman
                            </span>
                            <span>
                                <i class="fas fa-lock me-1"></i>
                                Data Terenkripsi
                            </span>
                            <span>
                                <i class="fas fa-clock me-1"></i>
                                Batas Bayar: {{ $payment->expired_at?->format('H:i') ?? '24 Jam' }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Midtrans_logo.svg/1200px-Midtrans_logo.svg.png" 
                                 alt="Midtrans" style="height: 20px; opacity: 0.6;">
                            <span class="ms-2">Powered by Midtrans</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Informasi -->
            <div class="alert alert-warning mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Perhatian:</strong> Anda memiliki waktu 24 jam untuk menyelesaikan pembayaran. 
                Jika melewati batas waktu, booking akan otomatis dibatalkan.
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .payment-icon {
        animation: pulse 2s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    .btn-primary {
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }
</style>
@endpush

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentForm = document.getElementById('payment-form');
        const payButton = document.getElementById('pay-button');

        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Disable button
            payButton.disabled = true;
            payButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memproses...';

            // Kirim request ke server
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success' && data.snap_token) {
                    // Buka popup Midtrans Snap
                    snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            // Redirect ke My Booking
                            window.location.href = '{{ route("booking.my") }}?payment_success=1';
                        },
                        onPending: function(result) {
                            // Redirect ke My Booking
                            window.location.href = '{{ route("booking.my") }}?payment_pending=1';
                        },
                        onError: function(result) {
                            // Redirect ke My Booking
                            window.location.href = '{{ route("booking.my") }}?payment_failed=1';
                        },
                        onClose: function() {
                            // User menutup popup tanpa membayar
                            payButton.disabled = false;
                            payButton.innerHTML = '<i class="fas fa-lock me-2"></i> Bayar Sekarang <span class="badge bg-light text-dark ms-2">Rp {{ number_format($booking->price_estimation, 0, ",", ".") }}</span>';
                        }
                    });
                } else {
                    alert('Gagal memproses pembayaran: ' + (data.message || 'Unknown error'));
                    payButton.disabled = false;
                    payButton.innerHTML = '<i class="fas fa-lock me-2"></i> Bayar Sekarang <span class="badge bg-light text-dark ms-2">Rp {{ number_format($booking->price_estimation, 0, ",", ".") }}</span>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                payButton.disabled = false;
                payButton.innerHTML = '<i class="fas fa-lock me-2"></i> Bayar Sekarang <span class="badge bg-light text-dark ms-2">Rp {{ number_format($booking->price_estimation, 0, ",", ".") }}</span>';
            });
        });
    });
</script>
@endpush