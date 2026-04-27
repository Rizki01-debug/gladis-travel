@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">✏️ Edit Pengeluaran</h4>
                <small class="text-muted">Perbarui data pengeluaran</small>
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

                <form method="POST" action="{{ route('finance.expense.update', $expense->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- JUDUL --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Judul Pengeluaran</label>
                            <input type="text" name="title" class="form-control"
                                value="{{ old('title', $expense->title) }}" required>
                        </div>

                        {{-- KATEGORI --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="category" class="form-control"
                                value="{{ old('category', $expense->category) }}" required>
                        </div>

                        {{-- JUMLAH --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah (Rp)</label>
                            <input type="number" name="amount" id="amount" class="form-control"
                                value="{{ old('amount', $expense->amount) }}" required>

                            <small class="text-muted" id="amount_preview">
                                Rp {{ number_format($expense->amount ?? 0, 0, ',', '.') }}
                            </small>
                        </div>

                        {{-- TANGGAL --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="expense_date" class="form-control"
                                value="{{ old('expense_date', \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d')) }}"
                                required>
                        </div>

                        {{-- DESKRIPSI --}}
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $expense->description) }}</textarea>
                        </div>

                    </div>

                    {{-- BUTTON --}}
                    <div class="mt-3 d-flex justify-content-end gap-2">
                        <a href="{{ route('finance.index') }}" class="btn btn-secondary">
                            Batal
                        </a>

                        <button class="btn btn-primary btn-premium">
                            💾 Update Pengeluaran
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
