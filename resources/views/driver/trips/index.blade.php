@extends('layouts.app')

@section('content')
    <h3 class="mb-3">🚗 Trip Saya</h3>

    <div class="card shadow-sm p-3">

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Status</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($trips as $trip)
                        <tr>
                            <td>#{{ $trip->id }}</td>

                            {{-- STATUS --}}
                            <td>
                                @if ($trip->trip_status == 'ongoing')
                                    <span class="badge bg-warning text-dark">Berjalan</span>
                                @elseif ($trip->trip_status == 'completed')
                                    <span class="badge bg-success">Selesai</span>
                                @else
                                    <span class="badge bg-secondary">{{ $trip->trip_status }}</span>
                                @endif
                            </td>

                            {{-- WAKTU --}}
                            <td>
                                {{ $trip->start_time ? \Carbon\Carbon::parse($trip->start_time)->format('d M Y H:i') : '-' }}
                            </td>

                            <td>
                                {{ $trip->end_time ? \Carbon\Carbon::parse($trip->end_time)->format('d M Y H:i') : '-' }}
                            </td>

                            {{-- AKSI --}}
                            <td>

                                {{-- 🔥 WAITING → MULAI --}}
                                @if ($trip->trip_status == 'waiting')
                                    <form action="{{ route('driver.trip.start', $trip->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-primary btn-sm" onclick="return confirm('Mulai trip ini?')">
                                            ▶ Mulai
                                        </button>
                                    </form>
                                @endif

                                {{-- 🔥 ONGOING → SELESAI --}}
                                @if ($trip->trip_status == 'ongoing')
                                    <form action="{{ route('driver.trip.complete', $trip->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-success btn-sm"
                                            onclick="return confirm('Selesaikan trip ini?')">
                                            ✔ Selesai
                                        </button>
                                    </form>
                                @endif

                                {{-- 🔥 COMPLETED --}}
                                @if ($trip->trip_status == 'completed')
                                    <span class="badge bg-success">✔ Selesai</span>
                                @endif

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Belum ada trip
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
