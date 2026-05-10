@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1">📋 Booking Saya</h4>
                <small class="text-muted">Riwayat dan status booking kamu</small>
            </div>
        </div>

        {{-- ================= TAB STATUS ================= --}}
        <div class="mb-3 d-flex gap-2 flex-wrap">
            @php
                $tabs = [
                    '' => 'Semua',
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ];
            @endphp

            @foreach ($tabs as $key => $label)
                <a href="{{ route('booking.my', array_merge(request()->all(), ['status' => $key])) }}"
                    class="btn btn-sm {{ request('status') == $key ? 'btn-primary' : 'btn-outline-primary' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- ================= FILTER ================= --}}
        <div class="card mb-3 p-3 border-0 shadow-sm">
            <form method="GET" class="row g-2 align-items-center">

                {{-- SEARCH --}}
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari rute / nomor WA..."
                        value="{{ request('search') }}">
                </div>

                {{-- STATUS DROPDOWN --}}
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach ($tabs as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- BUTTON --}}
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary">
                        🔍 Filter
                    </button>

                    <a href="{{ route('booking.my') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>

            </form>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="card card-premium border-0">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">

                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th class="text-start">Rute</th>
                                <th>Kursi</th>
                                <th>Pickup</th>
                                <th>WA</th>
                                <th>Tanggal</th>
                                <th>Jarak</th>
                                <th>Harga</th>
                                <th>Status</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($bookings as $booking)
                                <tr>

                                    <td class="text-center fw-semibold">
                                        #{{ $booking->id }}
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $booking->schedule->origin->name ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            → {{ $booking->schedule->destination->name ?? '-' }}
                                        </small>
                                    </td>

                                    <td class="text-center">
                                        @forelse ($booking->seats as $seat)
                                            <span class="badge-soft bg-primary text-white">
                                                {{ $seat->seat_number }}
                                            </span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </td>

                                    <td class="text-center">
                                        @if ($booking->pickup_type === 'meeting_point')
                                            <span class="badge-soft bg-light border text-dark">
                                                📍 {{ $booking->meetingPoint->name ?? '-' }}
                                            </span>
                                        @else
                                            <span class="badge-soft bg-info text-dark">
                                                🗺️ Dijemput
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if ($booking->phone)
                                            <a href="https://wa.me/{{ $booking->phone }}" target="_blank"
                                                class="btn btn-success btn-sm">
                                                💬
                                            </a>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($booking->departure_date)->format('d M Y') }}
                                    </td>

                                    <td class="text-center">
                                        {{ number_format($booking->distance_km, 1) }} KM
                                    </td>

                                    <td class="text-success text-center fw-semibold">
                                        Rp {{ number_format($booking->price_estimation, 0, ',', '.') }}
                                    </td>

                                    <td class="text-center">
                                        @php
                                            $statusClass = match ($booking->status) {
                                                'pending' => 'bg-warning text-dark',
                                                'confirmed' => 'bg-primary text-white',
                                                'completed' => 'bg-success text-white',
                                                'cancelled' => 'bg-danger text-white',
                                                default => 'bg-secondary text-white',
                                            };
                                        @endphp

                                        <span class="badge-soft {{ $statusClass }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        @if ($booking->status === 'pending')
                                            <form action="{{ route('booking.cancel', $booking->id) }}" method="POST"
                                                onsubmit="return confirmCancel(this)">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-danger btn-sm btn-cancel">
                                                    ❌ Cancel
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        🚫 Belum ada booking
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

                {{-- ================= PAGINATION ================= --}}
                @if ($bookings->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div class="text-muted small">
                            Menampilkan {{ $bookings->firstItem() }} - {{ $bookings->lastItem() }}
                            dari {{ $bookings->total() }} data
                        </div>

                        <div>
                            {{ $bookings->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                @endif

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function confirmCancel(form) {
            if (!confirm('Yakin ingin membatalkan booking ini?')) return false;

            const btn = form.querySelector('.btn-cancel');
            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Processing...';
            }
            return true;
        }
    </script>
@endpush
