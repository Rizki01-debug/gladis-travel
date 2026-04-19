@extends('layouts.app')

@section('content')
    <div class="container-custom fade-in">

        {{-- HEADER --}}
        <div class="mb-4">
            <h4 class="fw-bold mb-1">🚐 Pilih Jadwal</h4>
            <small class="text-muted">Pilih jadwal keberangkatan yang tersedia</small>
        </div>

        <div class="card-premium">

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">

                        <thead>
                            <tr>
                                <th>Rute</th>
                                <th>Waktu Berangkat</th>
                                <th>Kendaraan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($schedules as $s)
                                <tr>

                                    {{-- RUTE --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $s->origin->name ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            → {{ $s->destination->name ?? '-' }}
                                        </small>
                                    </td>

                                    {{-- WAKTU --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ \Carbon\Carbon::parse($s->departure_time)->format('H:i') }}
                                        </div>
                                        <small class="text-muted">
                                            Jam keberangkatan
                                        </small>
                                    </td>

                                    {{-- KENDARAAN --}}
                                    <td>
                                        @if ($s->vehicle)
                                            <span class="badge bg-light text-dark border">
                                                🚐 {{ $s->vehicle->name ?? 'Kendaraan' }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-end">
                                        <a href="{{ route('booking.create', $s->id) }}"
                                            class="btn btn-primary-custom btn-premium">
                                            🚀 Pesan
                                        </a>
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            🚫 Belum ada jadwal tersedia
                                        </div>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>

            </div>

        </div>

    </div>
@endsection
