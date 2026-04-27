@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">➕ Tambah Pengeluaran</h4>
                <small class="text-muted">Input data pengeluaran operasional</small>
            </div>

            <a href="{{ route('finance.index') }}" class="btn btn-secondary btn-sm">
                ← Kembali
            </a>
        </div>

        {{-- ================= ERROR ================= --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <b>Terjadi kesalahan:</b>
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ================= FORM ================= --}}
        <div class="card card-premium border-0">
            <div class="card-body">

                <form method="POST" action="{{ route('finance.expense.store') }}">
                    @csrf

                    <div class="row">

                        {{-- JUDUL --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Judul Pengeluaran</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}"
                                placeholder="Contoh: Servis Kendaraan" required>
                        </div>

                        {{-- KATEGORI --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="category" class="form-control" value="{{ old('category') }}"
                                placeholder="Contoh: Maintenance" required>
                        </div>

                        {{-- JUMLAH --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah (Rp)</label>
                            <input type="number" name="amount" id="amount" class="form-control"
                                value="{{ old('amount') }}" placeholder="Masukkan nominal" required>
                            <small class="text-muted" id="amount_preview">Rp 0</small>
                        </div>

                        {{-- TANGGAL --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Pengeluaran</label>
                            <input type="date" name="expense_date" class="form-control"
                                value="{{ old('expense_date', now()->format('Y-m-d')) }}" required>
                        </div>

                        {{-- DESKRIPSI --}}
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi (Opsional)</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Tambahkan keterangan...">{{ old('description') }}</textarea>
                        </div>

                    </div>

                    {{-- BUTTON --}}
                    <div class="mt-3 d-flex justify-content-end gap-2">
                        <a href="{{ route('finance.index') }}" class="btn btn-secondary">
                            Batal
                        </a>

                        <button class="btn btn-success btn-premium">
                            💾 Simpan Pengeluaran
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        const amountInput = document.getElementById('amount');
        const preview = document.getElementById('amount_preview');

        function formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        amountInput.addEventListener('input', function() {
            let val = this.value || 0;
            preview.innerText = 'Rp ' + formatRupiah(val);
        });
    </script>
@endpush
