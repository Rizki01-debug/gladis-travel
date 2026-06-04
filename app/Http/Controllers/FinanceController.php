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

        $expenses = Expense::latest()->paginate(10);

        // 🔥 GRAFIK (OPTIMIZED)
        $chartData = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->where('type', 'income')
            ->where('status', 'paid')
            ->groupByRaw('DATE(created_at)')
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
            'amount' => 'required|numeric|min:1',
            'category' => 'required|string|max:100',
            'expense_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $expense = Expense::create($validated);

        logActivity('Tambah Pengeluaran', 'ID: ' . $expense->id);

        return redirect()->route('finance.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan!');
    }

    // ================= EDIT =================
    public function editExpense($id)
    {
        $this->authorizeFinance();

        $expense = Expense::findOrFail($id);

        return view('finance.edit_expense', compact('expense'));
    }

    // ================= UPDATE =================
    public function updateExpense(Request $request, $id)
    {
        $this->authorizeFinance();

        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'category' => 'required|string|max:100',
            'expense_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $expense->update($validated);

        logActivity('Update Pengeluaran', 'ID: ' . $expense->id);

        return redirect()->route('finance.index')
            ->with('success', 'Pengeluaran berhasil diperbarui!');
    }

    // ================= DELETE =================
    public function deleteExpense($id)
    {
        $this->authorizeFinance();

        $expense = Expense::findOrFail($id);
        $expense->delete();

        logActivity('Hapus Pengeluaran', 'ID: ' . $id);

        return back()->with('success', 'Pengeluaran berhasil dihapus!');
    }

    // ================= REPORT =================
    public function report(Request $request)
    {
        $this->authorizeFinance();

        $start = $request->start_date;
        $end = $request->end_date;

        // ================= VALIDASI TANGGAL =================
        if ($start && $end && $start > $end) {
            return back()->withErrors('Tanggal tidak valid');
        }

        // ================= QUERY BASE =================
        $transactionQuery = Transaction::where('type', 'income')
            ->where('status', 'paid');

        $expenseQuery = Expense::query();

        // ================= FILTER =================
        if ($start && $end) {
            $transactionQuery->whereBetween('created_at', [
                $start . ' 00:00:00',
                $end . ' 23:59:59'
            ]);

            $expenseQuery->whereBetween('expense_date', [$start, $end]);
        }

        // ================= PAGINATION =================
        $transactions = $transactionQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $expenses = $expenseQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // ================= SUMMARY (AMBIL DARI QUERY, BUKAN PAGINATE) =================
        $totalIncome = (clone $transactionQuery)->sum('amount');
        $totalExpense = (clone $expenseQuery)->sum('amount');
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
            )
            ->get();

        $expenses = Expense::when(
            $start && $end,
            fn($q) =>
            $q->whereBetween('expense_date', [$start, $end])
        )->get();

        $data = [
            'transactions' => $transactions,
            'expenses' => $expenses,
            'totalIncome' => $transactions->sum('amount'),
            'totalExpense' => $expenses->sum('amount'),
            'balance' => $transactions->sum('amount') - $expenses->sum('amount'),
            'start' => $start,
            'end' => $end
        ];

        logActivity('Export PDF', 'Periode: ' . ($start ?? '-') . ' s/d ' . ($end ?? '-'));

        return Pdf::loadView('finance.pdf', $data)
            ->download('laporan-keuangan.pdf');
    }

    // ================= SETORAN =================
    public function setoran()
    {
        $this->authorizeFinance();

        // 🔥 FIX N+1 QUERY (WAJIB)
        $earnings = DriverEarning::with([
            'driver',
            'booking.user',
            'booking.schedule.origin',
            'booking.schedule.destination'
        ])
            ->latest()
            ->paginate(10);

        return view('finance.setoran', compact('earnings'));
    }

    // ================= CONFIRM SETORAN =================
    public function confirmSetoran($id)
    {
        $this->authorizeFinance();

        try {

            DB::transaction(function () use ($id) {

                $earning = DriverEarning::lockForUpdate()
                    ->findOrFail($id);

                // ================= VALIDASI STATUS =================
                if ($earning->status === 'paid') {
                    throw new \Exception('Setoran sudah dikonfirmasi');
                }

                if ($earning->status === 'cancelled') {
                    throw new \Exception('Booking telah dibatalkan dan tidak dapat dikonfirmasi');
                }

                // ================= UPDATE STATUS =================
                $earning->update([
                    'status' => 'paid'
                ]);

                // ================= CEK DUPLIKASI TRANSAKSI =================
                $exists = Transaction::where('booking_id', $earning->booking_id)
                    ->where('type', 'income')
                    ->exists();

                if (!$exists) {

                    Transaction::create([
                        'booking_id' => $earning->booking_id,
                        'amount' => $earning->amount,
                        'type' => 'income',
                        'payment_method' => 'cash',
                        'status' => 'paid'
                    ]);
                }

                logActivity(
                    'Setoran Driver',
                    'Earning ID: ' . $earning->id
                );
            });

            return back()->with(
                'success',
                'Setoran berhasil dikonfirmasi!'
            );
        } catch (\Exception $e) {

            return back()->withErrors(
                $e->getMessage()
            );
        }
    }
}
