<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Booking;
use App\Models\Expense;

class FinanceController extends Controller
{
    // 🔒 HELPER PROTECTION (biar gak ngulang terus)
    private function authorizeAdmin()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= DASHBOARD =================
    public function index()
    {
        $this->authorizeAdmin();

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

    // ================= FORM TAMBAH =================
    public function createExpense()
    {
        $this->authorizeAdmin();

        return view('finance.create_expense');
    }

    // ================= SIMPAN =================
    public function storeExpense(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'category' => 'required|string|max:100',
            'expense_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        Expense::create($validated);

        return redirect()->route('finance.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan!');
    }

    // ================= REPORT =================
    public function report(Request $request)
    {
        $this->authorizeAdmin();

        $start = $request->start_date;
        $end = $request->end_date;

        $bookings = Booking::when($start && $end, function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })->get();

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