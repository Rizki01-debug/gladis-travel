@extends('layouts.app')

@section('content')

<h3 class="mb-3">✏️ Edit Pengeluaran</h3>

@if ($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('finance.expense.update', $expense->id) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="title" class="form-control"
            value="{{ $expense->title }}" required>
    </div>

    <div class="mb-3">
        <label>Jumlah</label>
        <input type="number" name="amount" class="form-control"
            value="{{ $expense->amount }}" required>
    </div>

    <div class="mb-3">
        <label>Kategori</label>
        <input type="text" name="category" class="form-control"
            value="{{ $expense->category }}" required>
    </div>

    <div class="mb-3">
        <label>Tanggal</label>
        <input type="date" name="expense_date" class="form-control"
            value="{{ $expense->expense_date }}" required>
    </div>

    <div class="mb-3">
        <label>Deskripsi</label>
        <textarea name="description" class="form-control">{{ $expense->description }}</textarea>
    </div>

    <button class="btn btn-primary">💾 Update</button>

</form>

@endsection