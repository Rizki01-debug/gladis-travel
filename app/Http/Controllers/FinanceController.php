<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Expense;

class FinanceController extends Controller
{
    // DASHBOARD KEUANGAN
    public function index()
    {
        $income = Booking::sum('price_estimation');
        $expense = Expense::sum('amount');

        $balance = $income - $expense;

        $expenses = Expense::latest()->get();

        return view('finance.index', compact(
            'income',
            'expense',
            'balance',
            'expenses'
        ));
    }

    // FORM TAMBAH PENGELUARAN
    public function createExpense()
    {
        return view('finance.create_expense');
    }

    // SIMPAN PENGELUARAN
    public function storeExpense(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'amount' => 'required|numeric',
            'category' => 'required',
            'expense_date' => 'required|date'
        ]);

        Expense::create([
            'title' => $request->title,
            'amount' => $request->amount,
            'category' => $request->category,
            'description' => $request->description,
            'expense_date' => $request->expense_date
        ]);

        return redirect()->route('finance.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan!');
    }

    public function report(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        // filter pemasukan
        $bookings = Booking::when($start && $end, function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })->get();

        // filter pengeluaran
        $expenses = Expense::when($start && $end, function ($q) use ($start, $end) {
            $q->whereBetween('expense_date', [$start, $end]);
        })->get();

        $totalIncome = $bookings->sum('price_estimation');
        $totalExpense = $expenses->sum('amount');

        $balance = $totalIncome - $totalExpense;

        return view('finance.report', compact(
            'bookings',
            'expenses',
            'totalIncome',
            'totalExpense',
            'balance',
            'start',
            'end'
        ));
    }
}
