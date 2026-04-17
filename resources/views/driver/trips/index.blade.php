@extends('layouts.app')

@section('content')
    <div class="container">

        <h3 class="mb-4">🚗 Trip Saya</h3>

        {{-- ALERT --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light text-center">
                            <tr>
                                <th>#</th>
                                <th>Penumpang</th>
                                <th>WA</th>
                                <th>Rute</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($trips as $trip)
                                @php
                                    $booking = $trip->booking;
                                @endphp

                                <tr>

                                    {{-- ID --}}
                                    <td class="text-center fw-bold">
                                        #{{ $trip->id }}
                                    </td>

                                    {{-- PENUMPANG --}}
                                    <td>
                                        👤 {{ optional($booking->user)->name ?? '-' }}
                                    </td>

                                    {{-- WHATSAPP --}}
                                    <td class="text-center">
                                        @if ($booking && $booking->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->phone) }}"
                                                target="_blank" class="btn btn-success btn-sm">
                                                💬 WA
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- RUTE --}}
                                    <td>
                                        <small>
                                            <b>{{ optional($booking->schedule->origin)->name ?? '-' }}</b>
                                            →
                                            <b>{{ optional($booking->schedule->destination)->name ?? '-' }}</b>
                                        </small>
                                    </td>

                                    {{-- TANGGAL --}}
                                    <td class="text-center">
                                        {{ $booking->formatted_date ?? '-' }}
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-center">
                                        @switch($trip->trip_status)
                                            @case('waiting')
                                                <span class="badge bg-secondary">Menunggu</span>
                                            @break

                                            @case('ongoing')
                                                <span class="badge bg-warning text-dark">Berjalan</span>
                                            @break

                                            @case('completed')
                                                <span class="badge bg-success">Selesai</span>
                                            @break

                                            @default
                                                <span class="badge bg-secondary">
                                                    {{ $trip->trip_status }}
                                                </span>
                                        @endswitch
                                    </td>

                                    {{-- MULAI --}}
                                    <td class="text-center">
                                        {{ $trip->start_time ? \Carbon\Carbon::parse($trip->start_time)->format('d M H:i') : '-' }}
                                    </td>

                                    {{-- SELESAI --}}
                                    <td class="text-center">
                                        {{ $trip->end_time ? \Carbon\Carbon::parse($trip->end_time)->format('d M H:i') : '-' }}
                                    </td>

                                    {{-- AKSI --}}
                                    <td>
                                        <div class="d-grid gap-1">

                                            {{-- START --}}
                                            @if ($trip->trip_status === 'waiting')
                                                <form action="{{ route('driver.trip.start', $trip->id) }}" method="POST"
                                                    onsubmit="return confirm('Mulai trip ini?')">
                                                    @csrf
                                                    <button class="btn btn-primary btn-sm w-100">
                                                        ▶ Mulai
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- COMPLETE --}}
                                            @if ($trip->trip_status === 'ongoing')
                                                <form action="{{ route('driver.trip.complete', $trip->id) }}"
                                                    method="POST" onsubmit="return confirm('Selesaikan trip ini?')">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm w-100">
                                                        ✔ Selesai
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- DONE --}}
                                            @if ($trip->trip_status === 'completed')
                                                <span class="text-success text-center fw-semibold">
                                                    ✔ Selesai
                                                </span>
                                            @endif

                                        </div>
                                    </td>

                                </tr>

                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <h6 class="text-muted">🚫 Belum ada trip</h6>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    @if (method_exists($trips, 'links'))
                        <div class="mt-3">
                            {{ $trips->links() }}
                        </div>
                    @endif

                </div>
            </div>

        </div>
    @endsection
