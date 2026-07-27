@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">🚗 Trip Saya</h4>
                <small class="text-muted">Kelola perjalanan yang sedang dan sudah selesai</small>
            </div>
        </div>

        {{-- ================= CARD ================= --}}
        <div class="card card-premium border-0">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">

                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th class="text-start">Penumpang</th>
                                <th>WA</th>
                                <th class="text-start">Rute</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th width="160">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($trips as $trip)
                                @php
                                    $booking = $trip->booking;
                                @endphp

                                <tr>

                                    {{-- ID --}}
                                    <td class="text-center fw-semibold">
                                        {{ $trip->id }}
                                    </td>

                                    {{-- PENUMPANG --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ optional($booking->user)->name ?? '-' }}
                                        </div>
                                    </td>

                                    {{-- WA --}}
                                    <td class="text-center">
                                        @if ($booking && $booking->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->phone) }}"
                                                target="_blank" class="btn btn-success btn-sm btn-premium"
                                                title="Chat WhatsApp">
                                                💬
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- RUTE --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ optional($booking->schedule->origin)->name ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            → {{ optional($booking->schedule->destination)->name ?? '-' }}
                                        </small>
                                    </td>

                                    {{-- TANGGAL --}}
                                    <td class="text-center">
                                        {{ $booking->formatted_date ?? '-' }}
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-center">
                                        @php
                                            $statusClass = match ($trip->trip_status) {
                                                'waiting' => 'bg-secondary text-white',
                                                'ongoing' => 'bg-warning text-dark',
                                                'completed' => 'bg-success text-white',
                                                default => 'bg-secondary text-white',
                                            };

                                            $statusLabel = match ($trip->trip_status) {
                                                'waiting' => 'Menunggu',
                                                'ongoing' => 'Berjalan',
                                                'completed' => 'Selesai',
                                                default => ucfirst($trip->trip_status),
                                            };
                                        @endphp

                                        <span class="badge-soft {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    {{-- MULAI --}}
                                    <td class="text-center small text-muted">
                                        {{ $trip->start_time ? \Carbon\Carbon::parse($trip->start_time)->format('d M H:i') : '-' }}
                                    </td>

                                    {{-- SELESAI --}}
                                    <td class="text-center small text-muted">
                                        {{ $trip->end_time ? \Carbon\Carbon::parse($trip->end_time)->format('d M H:i') : '-' }}
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-center">

                                        <div class="d-flex flex-column gap-1">

                                            {{-- START --}}
                                            @if ($trip->trip_status === 'waiting')
                                                <form action="{{ route('driver.trip.start', $trip->id) }}" method="POST"
                                                    onsubmit="return confirmAction(this,'Mulai trip ini?')">
                                                    @csrf
                                                    <button class="btn btn-primary btn-sm btn-premium w-100">
                                                        ▶ Mulai
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- COMPLETE --}}
                                            @if ($trip->trip_status === 'ongoing')
                                                <form action="{{ route('driver.trip.complete', $trip->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirmAction(this,'Selesaikan trip ini?')">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm btn-premium w-100">
                                                        ✔ Selesai
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- DONE --}}
                                            @if ($trip->trip_status === 'completed')
                                                <span class="text-success fw-semibold small">
                                                    ✔ Selesai
                                                </span>
                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            🚫 Belum ada trip
                                        </div>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

                {{-- ================= PAGINATION ================= --}}
                @if ($trips->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div class="text-muted small">
                            Menampilkan {{ $trips->firstItem() }} - {{ $trips->lastItem() }}
                            dari {{ $trips->total() }} data
                        </div>

                        <div>
                            {{ $trips->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                @endif

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function confirmAction(form, message) {

            if (!confirm(message)) return false;

            const btn = form.querySelector('button');

            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Processing...';
            }

            return true;
        }
    </script>
@endpush
