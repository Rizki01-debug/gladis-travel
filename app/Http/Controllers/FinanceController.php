<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Expense;
use App\Models\Transaction;

class FinanceController extends Controller
{
    // ================= AUTH =================
    private function authorizeFinance()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !($user->isAdmin() || $user->isSuperAdmin())) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= DASHBOARD =================
    public function index()
    {
        $this->authorizeFinance();

        // ✅ TOTAL PEMASUKAN
        $income = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->sum('amount');

        // ✅ TOTAL PENGELUARAN
        $expense = Expense::sum('amount');

        // ✅ SALDO
        $balance = $income - $expense;

        // ✅ LIST PENGELUARAN
        $expenses = Expense::latest()->get();

        // ✅ DATA GRAFIK (PER HARI)
        $chartData = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(amount) as total')
        )
            ->where('type', 'income')
            ->where('status', 'paid')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        return view('finance.index', compact(
            'income',
            'expense',
            'balance',
            'expenses',
            'chartData'
        ));
    }

    // ================= FORM TAMBAH =================
    public function createExpense()
    {
        $this->authorizeFinance();

        return view('finance.create_expense');
    }

    // ================= SIMPAN =================
    public function storeExpense(Request $request)
    {
        $this->authorizeFinance();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'expense_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        Expense::create($validated);

        return redirect()
            ->route('finance.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan!');
    }

    // ================= REPORT =================
    public function report(Request $request)
    {
        $this->authorizeFinance();

        $start = $request->start_date;
        $end = $request->end_date;

        // ✅ DATA PEMASUKAN
        $transactions = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
            ->latest()
            ->get();

        // ✅ DATA PENGELUARAN
        $expenses = Expense::when($start && $end, function ($q) use ($start, $end) {
            $q->whereBetween('expense_date', [$start, $end]);
        })
            ->latest()
            ->get();

        // ✅ TOTAL
        $totalIncome = $transactions->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $balance = $totalIncome - $totalExpense;

        return view('finance.report', compact(
            'transactions',
            'expenses',
            'totalIncome',
            'totalExpense',
            'balance',
            'start',
            'end'
        ));
    }

    // ================= EXPORT PDF =================
    public function exportPdf(Request $request)
    {
        $this->authorizeFinance();

        $start = $request->start_date;
        $end = $request->end_date;

        // ✅ TRANSACTIONS
        $transactions = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
            ->get();

        // ✅ EXPENSES
        $expenses = Expense::when($start && $end, function ($q) use ($start, $end) {
            $q->whereBetween('expense_date', [$start, $end]);
        })->get();

        // ✅ TOTAL
        $totalIncome = $transactions->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // ✅ GENERATE PDF
        $pdf = Pdf::loadView('finance.pdf', [
            'transactions' => $transactions,
            'expenses' => $expenses,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'start' => $start,
            'end' => $end
        ]);

        return $pdf->download('laporan-keuangan.pdf');
    }

    // ================= LIST SETORAN =================
    public function setoran()
    {
        $this->authorizeFinance();

        $transactions = Transaction::with('booking')
            ->where('type', 'income')
            ->where('status', 'unpaid')
            ->latest()
            ->get();

        return view('finance.setoran', compact('transactions'));
    }

    // ================= KONFIRMASI SETORAN =================
    public function confirmSetoran($id)
    {
        $this->authorizeFinance();

        $transaction = Transaction::findOrFail($id);

        $transaction->update([
            'status' => 'paid'
        ]);

        return back()->with('success', 'Setoran berhasil dikonfirmasi!');
    }
}
