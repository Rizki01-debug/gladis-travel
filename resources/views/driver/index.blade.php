@extends('layouts.app')

@section('content')
    <div class="container">

        <h3 class="mb-4">📋 Booking Masuk</h3>

        {{-- ALERT --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-hover">

                        <thead class="table-light text-center">
                            <tr>
                                <th>ID</th>
                                <th>Penumpang</th>
                                <th>WA</th> {{-- 🔥 TAMBAH --}}
                                <th>Rute</th>
                                <th>Tanggal</th>
                                <th>Kursi</th>
                                <th>Status</th>
                                <th width="200">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($bookings as $b)
                                <tr>

                                    {{-- ID --}}
                                    <td class="text-center fw-bold">
                                        #{{ $b->id }}
                                    </td>

                                    {{-- PENUMPANG --}}
                                    <td>
                                        {{ $b->user->name ?? '-' }}
                                    </td>

                                    {{-- WA --}}
                                    <td class="text-center">
                                        @if ($b->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $b->phone) }}"
                                                target="_blank" class="btn btn-success btn-sm">
                                                💬 WA
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- RUTE --}}
                                    <td>
                                        <b>{{ optional($b->schedule->origin)->name ?? '-' }}</b>
                                        →
                                        <b>{{ optional($b->schedule->destination)->name ?? '-' }}</b>
                                    </td>

                                    {{-- TANGGAL --}}
                                    <td class="text-center">
                                        {{ $b->formatted_date ?? '-' }}
                                    </td>

                                    {{-- KURSI --}}
                                    <td>
                                        @forelse ($b->seats as $seat)
                                            <span class="badge bg-primary">
                                                {{ $seat->seat_number }}
                                            </span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-center">
                                        @switch($b->status)
                                            @case('pending')
                                                <span class="badge bg-warning text-dark">Menunggu</span>
                                            @break

                                            @case('confirmed')
                                                <span class="badge bg-info">Dikonfirmasi</span>
                                            @break

                                            @case('completed')
                                                <span class="badge bg-success">Selesai</span>
                                            @break

                                            @case('rejected')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @break

                                            @default
                                                <span class="badge bg-secondary">{{ $b->status }}</span>
                                        @endswitch
                                    </td>

                                    {{-- AKSI --}}
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">

                                            {{-- DETAIL --}}
                                            <a href="{{ route('driver.show', $b->id) }}" class="btn btn-info btn-sm">
                                                Detail
                                            </a>

                                            {{-- AKSI CEPAT --}}
                                            @if ($b->status == 'pending')
                                                <form action="{{ route('driver.confirm', $b->id) }}" method="POST"
                                                    onsubmit="return confirm('Konfirmasi booking ini?')">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm">
                                                        ✔
                                                    </button>
                                                </form>

                                                <form action="{{ route('driver.reject', $b->id) }}" method="POST"
                                                    onsubmit="return confirm('Tolak booking ini?')">
                                                    @csrf
                                                    <button class="btn btn-danger btn-sm">
                                                        ✖
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    </td>

                                </tr>

                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <h5 class="text-muted">🚫 Tidak ada booking</h5>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    @if (method_exists($bookings, 'links'))
                        <div class="mt-3">
                            {{ $bookings->links() }}
                        </div>
                    @endif

                </div>
            </div>

        </div>
    @endsection
