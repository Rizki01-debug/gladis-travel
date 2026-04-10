@extends('layouts.app')

@section('content')

<h3 class="mb-4">📋 Booking Saya</h3>

<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Rute</th>
                <th>Kursi</th>
                <th>Tanggal</th>
                <th>Jarak</th>
                <th>Harga</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($bookings as $booking)
                <tr>
                    {{-- ID --}}
                    <td>{{ $booking->id }}</td>

                    {{-- RUTE --}}
                    <td>
                        <b>{{ $booking->schedule->origin->name ?? '-' }}</b>
                        →
                        <b>{{ $booking->schedule->destination->name ?? '-' }}</b>
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

                    {{-- TANGGAL --}}
                    <td>
                        {{ \Carbon\Carbon::parse($booking->departure_date)->format('d M Y') }}
                    </td>

                    {{-- JARAK --}}
                    <td>
                        {{ $booking->distance_km ?? 0 }} KM
                    </td>

                    {{-- HARGA --}}
                    <td>
                        Rp {{ number_format($booking->price_estimation ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- STATUS --}}
                    <td>
                        @php
                            $color =
                                $booking->status == 'pending' ? 'warning' :
                                ($booking->status == 'confirmed' ? 'info' :
                                ($booking->status == 'completed' ? 'success' : 'secondary'));
                        @endphp

                        <span class="badge bg-{{ $color }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        🚫 Belum ada booking
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection