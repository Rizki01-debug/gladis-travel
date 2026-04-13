@extends('layouts.app')

@section('content')

<h3>Tambah Pengeluaran</h3>

<form method="POST" action="{{ route('finance.expense.store') }}">
@csrf

<input type="text" name="title" class="form-control mb-2" placeholder="Judul" required>

<input type="number" name="amount" class="form-control mb-2" placeholder="Jumlah" required>

<input type="text" name="category" class="form-control mb-2" placeholder="Kategori" required>

<textarea name="description" class="form-control mb-2" placeholder="Deskripsi"></textarea>

<input type="date" name="expense_date" class="form-control mb-2" required>

<button class="btn btn-success">Simpan</button>

</form>

@endsection