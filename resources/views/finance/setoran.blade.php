@extends('layouts.app')

@section('content')
    <h3 class="mb-4">💰 Setoran Driver</h3>

    <div class="card shadow-sm p-3">

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>#</th>
                        <th>Driver</th>
                        <th>Penumpang</th>
                        <th>Rute</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th width="160">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($earnings as $e)
                        <tr>

                            {{-- ID --}}
                            <td class="text-center">#{{ $e->id }}</td>

                            {{-- DRIVER --}}
                            <td>
                                👨‍✈️ {{ $e->driver->name ?? '-' }}
                            </td>

                            {{-- PENUMPANG --}}
                            <td>
                                👤 {{ $e->booking->user->name ?? '-' }}
                            </td>

                            {{-- RUTE --}}
                            <td>
                                <small>
                                    {{ optional($e->booking->schedule->origin)->name ?? '-' }}
                                    →
                                    {{ optional($e->booking->schedule->destination)->name ?? '-' }}
                                </small>
                            </td>

                            {{-- JUMLAH --}}
                            <td>
                                Rp {{ number_format($e->amount, 0, ',', '.') }}
                            </td>

                            {{-- STATUS --}}
                            <td class="text-center">
                                @if ($e->status == 'paid')
                                    <span class="badge bg-success">Sudah Setor</span>
                                @else
                                    <span class="badge bg-warning text-dark">Belum Setor</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center">

                                @if ($e->status == 'unpaid')
                                    <form action="{{ route('finance.setoran.confirm', $e->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-success btn-sm"
                                            onclick="return confirm('Konfirmasi setoran driver ini?')">
                                            ✔ Konfirmasi
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">✔ Done</span>
                                @endif

                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <h6 class="text-muted">🚫 Tidak ada setoran</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 🔥 PAGINATION --}}
        <div class="mt-3">
            {{ $earnings->links() ?? '' }}
        </div>

    </div>
@endsection
