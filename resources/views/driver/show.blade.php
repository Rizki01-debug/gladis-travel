@extends('layouts.app')

@section('content')
<div class="container">

    <h3 class="mb-4">🚗 Detail Booking</h3>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            @php
                $schedule = $booking->schedule;
                $cleanPhone = $booking->phone 
                    ? preg_replace('/[^0-9]/', '', $booking->phone) 
                    : null;
            @endphp

            <div class="row g-3">

                {{-- ================= PENUMPANG ================= --}}
                <div class="col-md-6">
                    <strong>👤 Penumpang</strong>
                    <div>{{ optional($booking->user)->name ?? '-' }}</div>
                </div>

                {{-- ================= WHATSAPP ================= --}}
                <div class="col-md-6">
                    <strong>📱 WhatsApp</strong>

                    @if ($booking->phone)
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">

                            <a href="https://wa.me/{{ $cleanPhone }}"
                               target="_blank"
                               class="btn btn-success btn-sm">
                                💬 Hubungi
                            </a>

                            <span class="badge bg-light text-dark border">
                                {{ $booking->phone }}
                            </span>

                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    onclick="copyWA('{{ $booking->phone }}')">
                                📋 Copy
                            </button>

                        </div>
                    @else
                        <div class="text-muted">-</div>
                    @endif
                </div>

                {{-- ================= RUTE ================= --}}
                <div class="col-md-6">
                    <strong>🛣️ Rute</strong>
                    <div>
                        <b>{{ optional($schedule->origin)->name ?? '-' }}</b>
                        →
                        <b>{{ optional($schedule->destination)->name ?? '-' }}</b>
                    </div>
                </div>

                {{-- ================= TANGGAL ================= --}}
                <div class="col-md-6">
                    <strong>📅 Tanggal</strong>
                    <div>{{ $booking->formatted_date ?? '-' }}</div>
                </div>

                {{-- ================= KENDARAAN ================= --}}
                <div class="col-md-6">
                    <strong>🚐 Kendaraan</strong>
                    <div>{{ optional($schedule->vehicle)->name ?? '-' }}</div>
                </div>

                {{-- ================= KURSI ================= --}}
                <div class="col-md-6">
                    <strong>💺 Kursi</strong>
                    <div>
                        @forelse ($booking->seats as $seat)
                            <span class="badge bg-primary">
                                {{ $seat->seat_number }}
                            </span>
                        @empty
                            <span class="text-muted">-</span>
                        @endforelse
                    </div>
                </div>

                {{-- ================= PICKUP ================= --}}
                <div class="col-md-6">
                    <strong>📌 Pickup</strong>
                    <div>
                        @if ($booking->pickup_type === 'meeting_point')
                            📍 {{ optional($booking->meetingPoint)->name ?? '-' }}
                        @else
                            🗺️ Dijemput
                            <br>
                            <small class="text-muted">
                                {{ $booking->pickup_maps ?? '-' }}
                            </small>
                        @endif
                    </div>
                </div>

                {{-- ================= HARGA ================= --}}
                <div class="col-md-6">
                    <strong>💰 Estimasi Harga</strong>
                    <div class="text-success fw-semibold">
                        Rp {{ number_format($booking->price_estimation ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="col-12">
                    <strong>Status</strong>
                    <div class="mt-1">
                        @switch($booking->status)
                            @case('pending')
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            @break

                            @case('confirmed')
                                <span class="badge bg-info">Dikonfirmasi</span>
                            @break

                            @case('completed')
                                <span class="badge bg-success">Selesai</span>
                            @break

                            {{-- @case('rejected')
                                <span class="badge bg-danger">Ditolak</span>
                            @break --}}

                            @default
                                <span class="badge bg-secondary">{{ $booking->status }}</span>
                        @endswitch
                    </div>
                </div>

            </div>

            {{-- ================= AKSI ================= --}}
            @if ($booking->status === 'pending')
                <div class="d-flex flex-wrap gap-2 mt-4">

                    <form action="{{ route('driver.confirm', $booking->id) }}"
                          method="POST"
                          onsubmit="return confirm('Terima booking ini?')">
                        @csrf
                        <button class="btn btn-success">
                            ✅ Terima
                        </button>
                    </form>

                    {{-- <form action="{{ route('driver.reject', $booking->id) }}"
                          method="POST"
                          onsubmit="return confirm('Tolak booking ini?')">
                        @csrf
                        <button class="btn btn-danger">
                            ❌ Tolak
                        </button>
                    </form> --}}

                </div>
            @else
                <div class="alert alert-info mt-4">
                    Booking sudah diproses.
                </div>
            @endif

        </div>
    </div>

</div>

{{-- ================= SCRIPT ================= --}}
<script>
function copyWA(phone) {
    navigator.clipboard.writeText(phone).then(() => {
        // 🔥 versi halus (tanpa alert ganggu UX)
        const btn = event.target;
        btn.innerText = '✔ Copied';
        setTimeout(() => btn.innerText = '📋 Copy', 1500);
    });
}
</script>

@endsection