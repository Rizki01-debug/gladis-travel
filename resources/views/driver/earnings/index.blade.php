@extends('layouts.app')

@section('content')
<div class="container-fluid fade-in">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

        <div>
            <h4 class="fw-bold mb-1">
                💰 Penghasilan Driver
            </h4>

            <small class="text-muted">
                Riwayat pemasukan dari setiap booking perjalanan
            </small>
        </div>

    </div>

    {{-- ================= SUMMARY CARD ================= --}}
    <div class="row g-3 mb-4">

        {{-- TOTAL --}}
        <div class="col-md-12">
            <div class="card card-premium border-0 shadow-sm h-100">

                <div class="card-body">

                    <small class="text-muted d-block mb-2">
                        Total Penghasilan
                    </small>

                    <h3 class="fw-bold mb-0 text-dark">
                        Rp {{ number_format($total ?? 0, 0, ',', '.') }}
                    </h3>

                </div>

            </div>
        </div>

        {{-- PAID --}}
        {{-- <div class="col-md-4">
            <div class="card card-premium border-0 shadow-sm h-100">

                <div class="card-body">

                    <small class="text-muted d-block mb-2">
                        Sudah Disetor
                    </small>

                    <h3 class="fw-bold text-success mb-0">
                        Rp {{ number_format($paid ?? 0, 0, ',', '.') }}
                    </h3>

                </div>

            </div>
        </div> --}}

        {{-- UNPAID --}}
        {{-- <div class="col-md-4">
            <div class="card card-premium border-0 shadow-sm h-100">

                <div class="card-body">

                    <small class="text-muted d-block mb-2">
                        Belum Disetor
                    </small>

                    <h3 class="fw-bold text-danger mb-0">
                        Rp {{ number_format($unpaid ?? 0, 0, ',', '.') }}
                    </h3>

                </div>

            </div>
        </div> --}}

    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card card-premium border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table align-middle table-hover mb-0">

                    {{-- TABLE HEADER --}}
                    <thead class="table-light">

                        <tr class="text-center align-middle">
                            <th width="10%">Booking</th>
                            <th width="25%">Driver</th>
                            <th width="20%">Nominal</th>
                            <th width="20%">Status</th>
                            <th width="25%">Tanggal</th>
                        </tr>

                    </thead>

                    {{-- TABLE BODY --}}
                    <tbody>

                        @forelse($earnings as $e)

                            <tr>

                                {{-- BOOKING --}}
                                <td class="text-center fw-semibold">
                                    {{ $e->booking_id }}
                                </td>

                                {{-- DRIVER --}}
                                <td>
                                    <div class="fw-semibold">
                                        {{ $e->driver->name ?? '-' }}
                                    </div>

                                    <small class="text-muted">
                                        Driver Travel
                                    </small>
                                </td>

                                {{-- NOMINAL --}}
                                <td class="text-center fw-bold text-success">
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

                                        <span class="badge rounded-pill bg-danger px-3 py-2">
                                            ⏳ Unpaid
                                        </span>

                                    @endif

                                </td>

                                {{-- TANGGAL --}}
                                <td class="text-center">

                                    <small class="text-muted">
                                        {{ optional($e->created_at)->format('d M Y H:i') }}
                                    </small>

                                </td>

                            </tr>

                        @empty

                            {{-- EMPTY STATE --}}
                            <tr>

                                <td colspan="5">

                                    <div class="text-center py-5">

                                        <div class="mb-3" style="font-size: 50px;">
                                            💸
                                        </div>

                                        <h6 class="fw-bold mb-1">
                                            Belum Ada Penghasilan
                                        </h6>

                                        <small class="text-muted">
                                            Data penghasilan driver akan muncul di sini
                                        </small>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- ================= PAGINATION ================= --}}
            @if(method_exists($earnings, 'hasPages') && $earnings->hasPages())

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