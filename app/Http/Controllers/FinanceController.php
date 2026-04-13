<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Expense;
use App\Models\Transaction;
use App\Models\DriverEarning;

class FinanceController extends Controller
{
    // ================= AUTH =================
    private function authorizeFinance()
    {
        if (!Auth::check() || !in_array(Auth::user()->role_id, [1, 2])) {
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

        // 🔥 GRAFIK
        $chartData = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->where('type', 'income')
            ->where('status', 'paid')
            ->groupBy('date')
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

        logActivity('Tambah Pengeluaran', 'ID: ' . $expense->id);

        return redirect()->route('finance.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan!');
    }

    // ================= REPORT =================
    public function report(Request $request)
    {
        $this->authorizeFinance();

        $start = $request->start_date;
        $end = $request->end_date;

        if ($start && $end && $start > $end) {
            return back()->withErrors('Tanggal tidak valid');
        }

        $transactions = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->when(
                $start && $end,
                fn($q) =>
                $q->whereBetween('created_at', [$start, $end])
            )
            ->latest()
            ->get();

        $expenses = Expense::when(
            $start && $end,
            fn($q) =>
            $q->whereBetween('expense_date', [$start, $end])
        )->latest()->get();

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
            ->when(
                $start && $end,
                fn($q) =>
                $q->whereBetween('created_at', [$start, $end])
            )->get();

        $expenses = Expense::when(
            $start && $end,
            fn($q) =>
            $q->whereBetween('expense_date', [$start, $end])
        )->get();

        $totalIncome = $transactions->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $balance = $totalIncome - $totalExpense;

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

    // ================= SETORAN (FIX TOTAL 🔥) =================
    public function setoran()
    {
        $this->authorizeFinance();

        $transactions = Transaction::with([
            'booking.user',
            'booking.trip.driver'
        ])
            ->where('type', 'income')
            ->latest()
            ->get();

        return view('finance.setoran', compact('transactions'));
    }

    // ================= CONFIRM SETORAN (FIX TOTAL 🔥) =================
    public function confirmSetoran($id)
    {
        $this->authorizeFinance();

        DB::transaction(function () use ($id) {

            $earning = DriverEarning::findOrFail($id);

            if ($earning->status === 'paid') {
                throw new \Exception('Sudah dikonfirmasi');
            }

            // ✅ update earning
            $earning->update([
                'status' => 'paid'
            ]);

            // ✅ MASUK KE TRANSACTION
            Transaction::create([
                'booking_id' => $earning->booking_id,
                'amount' => $earning->amount,
                'type' => 'income',
                'payment_method' => 'cash',
                'status' => 'paid'
            ]);

            logActivity('Setoran Driver', 'Earning ID: ' . $earning->id);
        });

        return back()->with('success', 'Setoran berhasil dikonfirmasi!');
    }
}
