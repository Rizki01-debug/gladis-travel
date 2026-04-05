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
        $user = Auth::user();

        // 🔥 pakai role_id (lebih stabil)
        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= DASHBOARD =================
    public function index()
    {
        $this->authorizeFinance();

        $income = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->sum('amount');

        $expense = Expense::sum('amount');

        $balance = $income - $expense;

        $expenses = Expense::latest()->get();

        // 🔥 grafik
        $chartData = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(amount) as total')
        )
            ->where('type', 'income')
            ->where('status', 'paid')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return view('finance.index', compact(
            'income',
            'expense',
            'balance',
            'expenses',
            'chartData'
        ));
    }

    // ================= CREATE =================
    public function createExpense()
    {
        $this->authorizeFinance();

        return view('finance.create_expense');
    }

    // ================= STORE =================
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

        $expense = Expense::create($validated);

        // 🔥 LOG
        logActivity('Tambah Pengeluaran', 'ID: ' . $expense->id);

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

        // 🔥 VALIDASI RANGE
        if ($start && $end && $start > $end) {
            return back()->withErrors('Tanggal tidak valid');
        }

        $transactions = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->when($start && $end, fn($q) =>
                $q->whereBetween('created_at', [$start, $end])
            )
            ->latest()
            ->get();

        $expenses = Expense::when($start && $end, fn($q) =>
            $q->whereBetween('expense_date', [$start, $end])
        )
            ->latest()
            ->get();

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

        $transactions = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->when($start && $end, fn($q) =>
                $q->whereBetween('created_at', [$start, $end])
            )
            ->get();

        $expenses = Expense::when($start && $end, fn($q) =>
            $q->whereBetween('expense_date', [$start, $end])
        )->get();

        $totalIncome = $transactions->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // 🔥 LOG
        logActivity('Export PDF', 'Periode: ' . ($start ?? '-') . ' s/d ' . ($end ?? '-'));

        $pdf = Pdf::loadView('finance.pdf', compact(
            'transactions',
            'expenses',
            'totalIncome',
            'totalExpense',
            'balance',
            'start',
            'end'
        ));

        return $pdf->download('laporan-keuangan.pdf');
    }

    // ================= SETORAN =================
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

    // ================= CONFIRM SETORAN =================
    public function confirmSetoran($id)
    {
        $this->authorizeFinance();

        $transaction = Transaction::findOrFail($id);

        // 🔥 anti double klik
        if ($transaction->status === 'paid') {
            return back()->withErrors('Sudah dikonfirmasi');
        }

        $transaction->update([
            'status' => 'paid'
        ]);

        // 🔥 LOG
        logActivity('Konfirmasi Setoran', 'ID: ' . $transaction->id);

        return back()->with('success', 'Setoran berhasil dikonfirmasi!');
    }
}