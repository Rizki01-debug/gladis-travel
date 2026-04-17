@extends('layouts.app')

@section('content')

<div class="container">

    <h3 class="mb-4">📋 Booking Saya</h3>

    {{-- ================= ALERT ================= --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table align-middle table-hover">

                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Rute</th>
                            <th>Kursi</th>
                            <th>Pickup</th>
                            <th>WA</th>
                            <th>Tanggal</th>
                            <th>Jarak</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($bookings as $booking)
                            <tr>

                                {{-- ID --}}
                                <td class="text-center fw-bold">
                                    #{{ $booking->id }}
                                </td>

                                {{-- RUTE --}}
                                <td>
                                    <div>
                                        <b>{{ $booking->schedule->origin->name ?? '-' }}</b>
                                        <br>
                                        <small class="text-muted">
                                            → {{ $booking->schedule->destination->name ?? '-' }}
                                        </small>
                                    </div>
                                </td>

                                {{-- KURSI --}}
                                <td>
                                    @forelse ($booking->seats as $seat)
                                        <span class="badge bg-primary">
                                            {{ $seat->seat_number }}
                                        </span>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>

                                {{-- PICKUP --}}
                                <td>
                                    @if ($booking->pickup_type === 'meeting_point')
                                        <span class="badge bg-light text-dark border">
                                            📍 {{ $booking->meetingPoint->name ?? '-' }}
                                        </span>
                                    @else
                                        <span class="badge bg-info text-dark">
                                            🗺️ Dijemput
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $booking->pickup_maps ?? '-' }}
                                        </small>
                                    @endif
                                </td>

                                {{-- WA --}}
                                <td class="text-center">
                                    @if($booking->phone)
                                        <a href="https://wa.me/{{ $booking->phone }}"
                                            target="_blank"
                                            class="btn btn-success btn-sm">
                                            💬 WA
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- TANGGAL --}}
                                <td class="text-center">
                                    {{ $booking->formatted_date }}
                                </td>

                                {{-- JARAK --}}
                                <td class="text-center">
                                    {{ number_format($booking->distance_km, 2) }} KM
                                </td>

                                {{-- HARGA --}}
                                <td class="fw-bold text-success">
                                    Rp {{ number_format($booking->price_estimation, 0, ',', '.') }}
                                </td>

                                {{-- STATUS --}}
                                <td class="text-center">
                                    <span class="badge bg-{{ $booking->status_color }}">
                                        {{ $booking->status_label }}
                                    </span>
                                </td>

                                {{-- AKSI --}}
                                <td class="text-center">

                                    @if ($booking->status === 'pending')
                                        <form action="{{ route('booking.cancel', $booking->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin membatalkan booking ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm">
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
                                <td colspan="10" class="text-center py-5">
                                    <h5 class="text-muted">🚫 Belum ada booking</h5>
                                    <small>Silakan lakukan booking terlebih dahulu</small>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- ================= PAGINATION ================= --}}
            @if(method_exists($bookings, 'links'))
                <div class="mt-3">
                    {{ $bookings->links() }}
                </div>
            @endif

        </div>
    </div>

</div>

@endsection