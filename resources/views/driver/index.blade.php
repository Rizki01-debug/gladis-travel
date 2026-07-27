@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">📋 Booking Masuk</h4>
                <small class="text-muted">Daftar booking yang perlu kamu proses</small>
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
                                <th>Kursi</th>
                                <th>Status</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($bookings as $b)
                                <tr>

                                    {{-- ID --}}
                                    <td class="text-center fw-semibold">
                                        {{ $b->id }}
                                    </td>

                                    {{-- PENUMPANG --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $b->user->name ?? '-' }}
                                        </div>
                                    </td>

                                    {{-- WA --}}
                                    <td class="text-center">
                                        @if ($b->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $b->phone) }}"
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
                                            {{ optional($b->schedule->origin)->name ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            → {{ optional($b->schedule->destination)->name ?? '-' }}
                                        </small>
                                    </td>

                                    {{-- TANGGAL --}}
                                    <td class="text-center">
                                        {{ $b->formatted_date ?? '-' }}
                                    </td>

                                    {{-- KURSI --}}
                                    <td class="text-center">
                                        @if ($b->seats && $b->seats->count())
                                            @foreach ($b->seats as $seat)
                                                <span class="badge-soft bg-primary text-white">
                                                    {{ $seat->seat_number }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-center">
                                        @php
                                            $statusClass = match ($b->status) {
                                                'pending' => 'bg-warning text-dark',
                                                'confirmed' => 'bg-primary text-white',
                                                'completed' => 'bg-success text-white',
                                                'cancelled' => 'bg-danger text-white',
                                                default => 'bg-secondary text-white',
                                            };
                                        @endphp

                                        <span class="badge-soft {{ $statusClass }}">
                                            {{ ucfirst($b->status) }}
                                        </span>
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-center">

                                        <div class="d-flex flex-wrap justify-content-center gap-1">

                                            {{-- DETAIL --}}
                                            <a href="{{ route('driver.show', $b->id) }}"
                                                class="btn btn-info btn-sm btn-premium">
                                                👁
                                            </a>

                                            {{-- ACTION --}}
                                            @if ($b->status === 'pending')
                                                {{-- CONFIRM --}}
                                                <form action="{{ route('driver.confirm', $b->id) }}" method="POST"
                                                    onsubmit="return confirmAction(this,'Terima booking ini?')">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm btn-premium">
                                                        ✔
                                                    </button>
                                                </form>

                                                {{-- REJECT --}}
                                                {{-- <form action="{{ route('driver.reject', $b->id) }}" method="POST"
                                                    onsubmit="return confirmAction(this,'Tolak booking ini?')">
                                                    @csrf
                                                    <button class="btn btn-danger btn-sm btn-premium">
                                                        ✖
                                                    </button>
                                                </form> --}}
                                            @endif

                                        </div>

                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            🚫 Tidak ada booking masuk
                                        </div>
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
                            {{ $bookings->links('pagination::bootstrap-5') }}
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
                btn.innerText = '...';
            }

            return true;
        }
    </script>
@endpush
