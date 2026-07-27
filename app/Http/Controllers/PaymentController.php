<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * @var MidtransService
     */
    protected $midtransService;

    /**
     * Constructor - inject MidtransService
     * 
     * @param MidtransService $midtransService
     */
    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Menampilkan halaman pilihan metode pembayaran
     * 
     * @param Booking $booking
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Booking $booking)
    {
        // Cek apakah booking ini sudah punya payment
        $payment = $booking->payment;

        // Jika sudah ada payment dan statusnya success, redirect ke halaman sukses
        if ($payment && $payment->status === 'success') {
            return redirect()->route('booking.my')
                ->with('success', 'Pembayaran sudah berhasil! Booking Anda sudah terkonfirmasi.');
        }

        // Jika payment expired, hapus atau buat ulang
        if ($payment && $payment->status === 'expired') {
            // Hapus payment lama untuk dibuat baru
            $payment->delete();
            $payment = null;
        }

        // Jika belum ada payment, buat payment baru dengan status pending
        if (!$payment) {
            // Ambil data customer dari user yang login
            $user = Auth::user();

            // Data customer dengan fallback
            $customerName = $user ? $user->name : 'Customer';
            $customerEmail = $user ? $user->email : 'customer@example.com';
            $customerPhone = $booking->phone ?? '081234567890';

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'order_id' => 'GLADIS-' . strtoupper(uniqid()),
                'gross_amount' => $booking->price_estimation ?? 0,
                'status' => 'pending',
                'customer_details' => [
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'phone' => $customerPhone
                ],
                'expired_at' => now()->addHours(24)
            ]);
        }

        return view('payment.index', compact('booking', 'payment'));
    }

    /**
     * Membuat transaksi Midtrans dan mendapatkan Snap Token
     * 
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSnap(Request $request, Booking $booking)
    {
        try {
            // Validasi booking
            if (!$booking) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking tidak ditemukan'
                ], 404);
            }

            // Dapatkan atau buat payment
            $payment = $booking->payment;
            if (!$payment) {
                $user = Auth::user();

                $customerName = $user ? $user->name : 'Customer';
                $customerEmail = $user ? $user->email : 'customer@example.com';
                $customerPhone = $booking->phone ?? '081234567890';

                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'order_id' => 'GLADIS-' . strtoupper(uniqid()),
                    'gross_amount' => $booking->price_estimation ?? 0,
                    'status' => 'pending',
                    'customer_details' => [
                        'name' => $customerName,
                        'email' => $customerEmail,
                        'phone' => $customerPhone
                    ],
                    'expired_at' => now()->addHours(24)
                ]);
            }

            // Siapkan data untuk Midtrans
            $customerDetails = $payment->customer_details ?? [];

            // Jika customer_details masih string, decode
            if (is_string($customerDetails)) {
                $customerDetails = json_decode($customerDetails, true) ?? [];
            }

            // Ambil data booking untuk item
            $itemName = 'Booking #' . $booking->id;
            if ($booking->schedule && $booking->schedule->origin && $booking->schedule->destination) {
                $itemName = $booking->schedule->origin->name . ' → ' . $booking->schedule->destination->name;
            }

            $transactionData = [
                'order_id' => $payment->order_id,
                'gross_amount' => (int) $payment->gross_amount,
                'customer_name' => $customerDetails['name'] ?? 'Customer',
                'customer_email' => $customerDetails['email'] ?? 'customer@example.com',
                'customer_phone' => $customerDetails['phone'] ?? '081234567890',
                'items' => [
                    [
                        'id' => 'BOOK-' . $booking->id,
                        'price' => (int) $payment->gross_amount,
                        'quantity' => 1,
                        'name' => $itemName
                    ]
                ],
                'metadata' => [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->booking_code ?? null
                ]
            ];

            // Panggil MidtransService untuk membuat transaksi
            $result = $this->midtransService->createTransaction($transactionData);

            if ($result['status'] === 'success') {
                // Update payment dengan snap token dan url
                $payment->update([
                    'snap_token' => $result['snap_token'],
                    'snap_url' => $result['redirect_url'] ?? null,
                    'payment_details' => $result['raw_response'] ?? []
                ]);

                return response()->json([
                    'status' => 'success',
                    'snap_token' => $result['snap_token'],
                    'redirect_url' => $result['redirect_url'] ?? null,
                    'order_id' => $payment->order_id,
                    'payment_id' => $payment->id,
                    'booking_id' => $booking->id
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'] ?? 'Gagal membuat transaksi'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Payment creation error: ' . $e->getMessage(), [
                'booking_id' => $booking->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Callback handler dari Midtrans
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
        try {
            // Log raw callback untuk debugging
            Log::info('Midtrans Callback Received', $request->all());

            // Verifikasi signature
            $isValid = $this->midtransService->verifyCallbackSignature($request->all());

            if (!$isValid) {
                Log::warning('Invalid callback signature', $request->all());
                return response('Invalid signature', 403);
            }

            // Proses callback
            $result = $this->midtransService->handleCallback($request->all());

            if ($result['status'] === 'success') {
                // Update payment status
                $payment = Payment::where('order_id', $result['order_id'])->first();

                if ($payment) {
                    // Ambil payment details existing
                    $existingDetails = [];
                    if ($payment->payment_details) {
                        if (is_string($payment->payment_details)) {
                            $existingDetails = json_decode($payment->payment_details, true) ?? [];
                        } else {
                            $existingDetails = $payment->payment_details ?? [];
                        }
                    }

                    // Merge dengan data baru
                    $mergedDetails = array_merge($existingDetails, $request->all());

                    // Update status payment
                    $payment->update([
                        'status' => $result['payment_status'],
                        'payment_details' => $mergedDetails,
                        'payment_method' => $result['payment_method'] ?? $payment->payment_method,
                        'payment_channel' => $result['payment_channel'] ?? $payment->payment_channel,
                        'bank' => $result['bank'] ?? $payment->bank,
                        'va_number' => $result['va_number'] ?? $payment->va_number,
                        'paid_at' => $result['payment_status'] === 'success' ? now() : $payment->paid_at
                    ]);

                    // Jika payment success, trigger events atau update related data
                    if ($result['payment_status'] === 'success') {
                        $this->handlePaymentSuccess($payment);
                    }
                }

                return response('OK', 200);
            } else {
                Log::error('Callback processing failed', $result);
                return response('Error processing callback', 500);
            }
        } catch (\Exception $e) {
            Log::error('Callback error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle pembayaran sukses
     * 
     * @param Payment $payment
     * @return void
     */
    private function handlePaymentSuccess(Payment $payment)
    {
        try {
            // 🔥 Panggil FinanceController untuk create income
            $financeController = new FinanceController();
            $financeController->createIncomeFromPayment($payment);

            // Update booking status
            $booking = $payment->booking;
            if ($booking && $booking->status === 'pending') {
                $booking->update([
                    'status' => 'confirmed'
                ]);
            }

            // Log success
            Log::info('Payment success handled', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'booking_id' => $payment->booking_id
            ]);
        } catch (\Exception $e) {
            Log::error('Error handling payment success: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Halaman sukses pembayaran - Redirect ke My Booking
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request)
    {
        $orderId = $request->get('order_id') ?? $request->get('orderId');
        
        if ($orderId) {
            $payment = Payment::where('order_id', $orderId)->first();
            if ($payment) {
                // Redirect ke my booking dengan pesan sukses
                return redirect()->route('booking.my')
                    ->with('success', 'Pembayaran berhasil! Booking #' . $payment->booking_id . ' sudah terkonfirmasi.');
            }
        }

        // Redirect ke my booking dengan pesan sukses umum
        return redirect()->route('booking.my')
            ->with('success', 'Pembayaran berhasil! Booking Anda sudah terkonfirmasi.');
    }

    /**
     * Halaman gagal pembayaran - Redirect ke My Booking
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function failed(Request $request)
    {
        $orderId = $request->get('order_id') ?? $request->get('orderId');
        
        if ($orderId) {
            $payment = Payment::where('order_id', $orderId)->first();
            if ($payment) {
                // Redirect ke my booking dengan pesan error
                return redirect()->route('booking.my')
                    ->with('error', 'Pembayaran gagal untuk Booking #' . $payment->booking_id . '. Silakan coba lagi.');
            }
        }

        // Redirect ke my booking dengan pesan error umum
        return redirect()->route('booking.my')
            ->with('error', 'Pembayaran gagal. Silakan coba lagi atau hubungi admin.');
    }

    /**
     * Cek status pembayaran
     * 
     * @param Request $request
     * @param string $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request, $orderId)
    {
        try {
            $payment = Payment::where('order_id', $orderId)->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not found'
                ], 404);
            }

            // Cek status ke Midtrans
            $result = $this->midtransService->checkStatus($orderId);

            if ($result['status'] === 'success') {
                // Update payment dengan status terbaru
                $payment->update([
                    'status' => $result['payment_status'],
                    'payment_details' => $result['raw_response'] ?? []
                ]);

                return response()->json([
                    'status' => 'success',
                    'payment_status' => $result['payment_status'],
                    'payment' => $payment
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'] ?? 'Failed to check status'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Status check error: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat cek status'
            ], 500);
        }
    }

    /**
     * Cancel/Expire payment
     * 
     * @param Request $request
     * @param string $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $orderId)
    {
        try {
            $payment = Payment::where('order_id', $orderId)->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not found'
                ], 404);
            }

            // Expire transaksi di Midtrans
            $result = $this->midtransService->expireTransaction($orderId);

            if ($result['status'] === 'success') {
                $payment->update([
                    'status' => 'expired'
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment has been cancelled'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'] ?? 'Failed to cancel payment'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Payment cancel error: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membatalkan pembayaran'
            ], 500);
        }
    }
}