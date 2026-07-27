<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Expense;
use App\Models\Transaction;
use App\Models\DriverEarning;
use App\Models\Payment;

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

        // 🔥 TAMPILKAN HANYA YANG UNPAID DAN CASH
        $earnings = DriverEarning::with([
            'driver',
            'booking.user',
            'booking.schedule.origin',
            'booking.schedule.destination'
        ])
        ->where('status', 'unpaid') // 🔥 HANYA YANG BELUM DIBAYAR
        ->whereHas('booking', function ($q) {
            $q->where('payment_method', 'cash'); // 🔥 HANYA CASH
        })
        ->latest()
        ->paginate(10);

        // 🔥 STATISTIK SETORAN
        $totalUnpaid = DriverEarning::where('status', 'unpaid')
            ->whereHas('booking', function ($q) {
                $q->where('payment_method', 'cash');
            })
            ->sum('amount');

        $totalPaid = DriverEarning::where('status', 'paid')
            ->whereHas('booking', function ($q) {
                $q->where('payment_method', 'cash');
            })
            ->sum('amount');

        return view('finance.setoran', compact('earnings', 'totalUnpaid', 'totalPaid'));
    }

    // ================= CONFIRM SETORAN =================
    public function confirmSetoran($id)
    {
        $this->authorizeFinance();

        try {
            DB::transaction(function () use ($id) {

                $earning = DriverEarning::lockForUpdate()
                    ->with('booking')
                    ->findOrFail($id);

                // ================= VALIDASI STATUS =================
                if ($earning->status === 'paid') {
                    throw new \Exception('Setoran sudah dikonfirmasi');
                }

                if ($earning->status === 'cancelled') {
                    throw new \Exception('Booking telah dibatalkan dan tidak dapat dikonfirmasi');
                }

                // 🔥 CEK METODE PEMBAYARAN
                $booking = $earning->booking;
                if ($booking && $booking->payment_method === 'online') {
                    throw new \Exception('Booking ini sudah dibayar online, tidak perlu konfirmasi manual');
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
                        'status' => 'paid',
                        'description' => 'Setoran tunai driver - Booking #' . $earning->booking_id
                    ]);
                }

                logActivity('Konfirmasi Setoran Driver (Cash)', 'Earning ID: ' . $earning->id);

            });

            return back()->with('success', 'Setoran berhasil dikonfirmasi!');
            
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    // ================= 🔥 TAMBAH: CREATE INCOME FROM PAYMENT =================
    /**
     * Create income transaction from payment success
     * 
     * @param Payment $payment
     * @return void
     */
    public function createIncomeFromPayment(Payment $payment)
    {
        try {
            // Validasi payment
            if (!$payment || $payment->status !== 'success') {
                Log::warning('Invalid payment for income creation', [
                    'payment_id' => $payment->id ?? null,
                    'status' => $payment->status ?? null
                ]);
                return;
            }

            // Cek apakah sudah ada transaction untuk booking ini
            $exists = Transaction::where('booking_id', $payment->booking_id)
                ->where('type', 'income')
                ->exists();

            if ($exists) {
                Log::info('Transaction already exists for booking', [
                    'booking_id' => $payment->booking_id
                ]);
                return;
            }

            // Buat transaction
            $transaction = Transaction::create([
                'booking_id' => $payment->booking_id,
                'amount' => $payment->gross_amount,
                'type' => 'income',
                'payment_method' => $payment->payment_method ?? 'online',
                'status' => 'paid',
                'description' => 'Pembayaran online via Midtrans - Order: ' . $payment->order_id,
                'payment_id' => $payment->id,
                'paid_at' => $payment->paid_at ?? now()
            ]);

            Log::info('Income created from payment success', [
                'payment_id' => $payment->id,
                'transaction_id' => $transaction->id,
                'booking_id' => $payment->booking_id,
                'amount' => $payment->gross_amount
            ]);

            // 🔥 Update status booking jika perlu
            $booking = $payment->booking;
            if ($booking && $booking->status === 'pending') {
                $booking->update([
                    'status' => 'confirmed'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error creating income from payment: ' . $e->getMessage(), [
                'payment_id' => $payment->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // ================= 🔥 TAMBAH: GET PAYMENT TRANSACTIONS =================
    /**
     * Get all payment transactions
     */
    public function paymentTransactions()
    {
        $this->authorizeFinance();

        $transactions = Transaction::with(['booking', 'payment'])
            ->where('type', 'income')
            ->whereNotNull('payment_id')
            ->latest()
            ->paginate(15);

        $totalPaymentIncome = Transaction::where('type', 'income')
            ->whereNotNull('payment_id')
            ->sum('amount');

        $totalCashIncome = Transaction::where('type', 'income')
            ->where('payment_method', 'cash')
            ->sum('amount');

        return view('finance.payment_transactions', compact(
            'transactions',
            'totalPaymentIncome',
            'totalCashIncome'
        ));
    }

    // ================= 🔥 TAMBAH: PAYMENT STATISTICS =================
    /**
     * Get payment statistics
     */
    public function paymentStatistics()
    {
        $this->authorizeFinance();

        $stats = [
            'total_online_payments' => Payment::where('status', 'success')->count(),
            'total_online_amount' => Payment::where('status', 'success')->sum('gross_amount'),
            'total_pending' => Payment::where('status', 'pending')->count(),
            'total_failed' => Payment::where('status', 'failed')->count(),
            'total_expired' => Payment::where('status', 'expired')->count(),
            'by_method' => Payment::where('status', 'success')
                ->selectRaw('payment_method, COUNT(*) as total, SUM(gross_amount) as amount')
                ->groupBy('payment_method')
                ->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}