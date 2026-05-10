@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <div>
                <h4 class="fw-bold mb-1">
                    💰 Setoran Driver
                </h4>

                <small class="text-muted">
                    Kelola setoran dan status penghasilan driver
                </small>
            </div>

        </div>

        {{-- ================= CARD ================= --}}
        <div class="card border-0 shadow-sm">

            <div class="card-body">

                {{-- ================= TABLE ================= --}}
                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        {{-- TABLE HEADER --}}
                        <thead class="table-light">

                            <tr class="text-center align-middle">
                                <th width="6%">#</th>
                                <th width="18%">Driver</th>
                                <th width="18%">Penumpang</th>
                                <th width="22%">Rute</th>
                                <th width="14%">Jumlah</th>
                                <th width="12%">Status</th>
                                <th width="10%">Aksi</th>
                            </tr>

                        </thead>

                        {{-- TABLE BODY --}}
                        <tbody>

                            @forelse ($earnings as $e)
                                <tr>

                                    {{-- ID --}}
                                    <td class="text-center fw-semibold">
                                        #{{ $e->id }}
                                    </td>

                                    {{-- DRIVER --}}
                                    <td>

                                        <div class="fw-semibold">
                                            👨‍✈️ {{ $e->driver->name ?? '-' }}
                                        </div>

                                        <small class="text-muted">
                                            Driver Travel
                                        </small>

                                    </td>

                                    {{-- PENUMPANG --}}
                                    <td>

                                        <div class="fw-semibold">
                                            👤 {{ $e->booking->user->name ?? '-' }}
                                        </div>

                                        <small class="text-muted">
                                            Penumpang
                                        </small>

                                    </td>

                                    {{-- RUTE --}}
                                    <td>

                                        <div class="small">

                                            <span class="fw-semibold">
                                                {{ optional($e->booking->schedule->origin)->name ?? '-' }}
                                            </span>

                                            <span class="mx-1">
                                                →
                                            </span>

                                            <span class="fw-semibold">
                                                {{ optional($e->booking->schedule->destination)->name ?? '-' }}
                                            </span>

                                        </div>

                                    </td>

                                    {{-- JUMLAH --}}
                                    <td class="fw-bold text-success text-center">
                                        Rp {{ number_format($e->amount ?? 0, 0, ',', '.') }}
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-center">

                                        @if ($e->status === 'paid')
                                            <span class="badge rounded-pill bg-success px-3 py-2">
                                                ✔ Paid
                                            </span>
                                        @elseif ($e->status === 'cancelled')
                                            <span class="badge rounded-pill bg-secondary px-3 py-2">
                                                ✖ Cancelled
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-warning text-dark px-3 py-2">
                                                ⏳ Unpaid
                                            </span>
                                        @endif

                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-center">

                                        @if ($e->status === 'unpaid')
                                            <form action="{{ route('finance.setoran.confirm', $e->id) }}" method="POST">

                                                @csrf

                                                <button class="btn btn-success btn-sm rounded-pill px-3"
                                                    onclick="return confirm('Konfirmasi setoran driver ini?')">

                                                    ✔ Konfirmasi

                                                </button>

                                            </form>
                                        @elseif ($e->status === 'cancelled')
                                            <span class="text-muted small">
                                                🚫 Dibatalkan
                                            </span>
                                        @else
                                            <span class="text-success small fw-semibold">
                                                ✔ Done
                                            </span>
                                        @endif

                                    </td>

                                </tr>

                            @empty

                                {{-- EMPTY STATE --}}
                                <tr>

                                    <td colspan="7">

                                        <div class="text-center py-5">

                                            <div style="font-size: 52px;" class="mb-3">
                                                💸
                                            </div>

                                            <h6 class="fw-bold mb-1">
                                                Tidak Ada Data Setoran
                                            </h6>

                                            <small class="text-muted">
                                                Data setoran driver akan tampil di sini
                                            </small>

                                        </div>

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{-- ================= PAGINATION ================= --}}
                @if (method_exists($earnings, 'hasPages') && $earnings->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">

                        <div class="small text-muted">

                            Menampilkan
                            {{ $earnings->firstItem() }}
                            -
                            {{ $earnings->lastItem() }}

                            dari

                            {{ $earnings->total() }}
                            data

                        </div>

                        <div>
                            {{ $earnings->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                @endif

            </div>

        </div>

    </div>
@endsection
