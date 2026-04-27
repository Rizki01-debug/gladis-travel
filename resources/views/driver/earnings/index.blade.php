@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">💰 Penghasilan Driver</h4>
                <small class="text-muted">Riwayat pemasukan dari setiap booking</small>
            </div>
        </div>

        {{-- ================= SUMMARY ================= --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0 h-100">
                    <small class="text-muted">Total</small>
                    <h4 class="fw-bold mb-0">
                        Rp {{ number_format($total ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0 h-100">
                    <small class="text-muted">Sudah Disetor</small>
                    <h4 class="fw-bold text-success mb-0">
                        Rp {{ number_format($paid ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-premium p-3 border-0 h-100">
                    <small class="text-muted">Belum Disetor</small>
                    <h4 class="fw-bold text-danger mb-0">
                        Rp {{ number_format($unpaid ?? 0, 0, ',', '.') }}
                    </h4>
                </div>
            </div>

        </div>

        {{-- ================= TABLE ================= --}}
        <div class="card card-premium border-0">

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">

                        <thead>
                            <tr class="text-center">
                                <th>#Booking</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($earnings as $e)
                                <tr>

                                    {{-- BOOKING --}}
                                    <td class="text-center fw-semibold">
                                        #{{ $e->booking_id }}
                                    </td>

                                    {{-- NOMINAL --}}
                                    <td class="fw-semibold text-success text-center">
                                        Rp {{ number_format($e->amount ?? 0, 0, ',', '.') }}
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-center">
                                        @if ($e->status === 'paid')
                                            <span class="badge-soft bg-success text-white">
                                                ✔ Paid
                                            </span>
                                        @else
                                            <span class="badge-soft bg-danger text-white">
                                                ✖ Unpaid
                                            </span>
                                        @endif
                                    </td>

                                    {{-- TANGGAL --}}
                                    <td class="text-center">
                                        <small class="text-muted">
                                            {{ $e->created_at ? \Carbon\Carbon::parse($e->created_at)->format('d M Y') : '-' }}
                                        </small>
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            💸 Belum ada data penghasilan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>

                {{-- ================= PAGINATION ================= --}}
                @if (method_exists($earnings, 'hasPages') && $earnings->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div class="text-muted small">
                            Menampilkan {{ $earnings->firstItem() }} - {{ $earnings->lastItem() }}
                            dari {{ $earnings->total() }} data
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
