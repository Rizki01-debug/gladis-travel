<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected $serverKey;
    protected $clientKey;
    protected $isProduction;
    protected $baseUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = config('midtrans.is_production', false);
        
        // 🔥 PERBAIKI URL - HAPUS DUPLIKASI "snap"
        $this->baseUrl = $this->isProduction 
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
        
        // Log untuk debugging
        Log::info('MidtransService initialized', [
            'base_url' => $this->baseUrl,
            'is_production' => $this->isProduction,
            'server_key_exists' => !empty($this->serverKey),
            'client_key_exists' => !empty($this->clientKey)
        ]);
    }

    /**
     * Create Snap Transaction
     */
    public function createTransaction(array $data): array
    {
        try {
            // 🔥 VALIDASI DATA
            if (empty($this->serverKey)) {
                Log::error('Midtrans Server Key is empty!');
                return [
                    'status' => 'error',
                    'message' => 'Server Key tidak ditemukan. Cek konfigurasi .env'
                ];
            }

            if (empty($data['order_id']) || empty($data['gross_amount'])) {
                return [
                    'status' => 'error',
                    'message' => 'Data transaksi tidak lengkap'
                ];
            }

            // 🔥 PAYLOAD YANG BENAR UNTUK MIDTRANS
            $payload = [
                'transaction_details' => [
                    'order_id' => $data['order_id'],
                    'gross_amount' => (int) $data['gross_amount']
                ],
                'customer_details' => [
                    'first_name' => $data['customer_name'] ?? 'Customer',
                    'email' => $data['customer_email'] ?? 'customer@example.com',
                    'phone' => $data['customer_phone'] ?? '081234567890'
                ],
                'item_details' => $data['items'] ?? [],
                'enabled_payments' => [
                    'credit_card',
                    'bank_transfer',
                    'qris',
                    'gopay',
                    'shopeepay',
                    'dana'
                ]
            ];

            // Tambahan metadata
            if (!empty($data['metadata'])) {
                $payload['metadata'] = $data['metadata'];
            }

            // 🔥 URL YANG BENAR: baseUrl + /transactions
            $url = $this->baseUrl . '/transactions';
            
            Log::info('Midtrans Request', [
                'url' => $url,
                'payload' => $payload,
                'server_key' => substr($this->serverKey, 0, 10) . '...'
            ]);

            // 🔥 KIRIM REQUEST
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post($url, $payload);

            // 🔥 LOG RESPONSE
            Log::info('Midtrans Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info('Midtrans Success', ['result' => $result]);
                
                return [
                    'status' => 'success',
                    'snap_token' => $result['token'] ?? null,
                    'redirect_url' => $result['redirect_url'] ?? null,
                    'raw_response' => $result
                ];
            }

            // 🔥 ERROR RESPONSE
            $errorBody = $response->json();
            Log::error('Midtrans API Error', [
                'status' => $response->status(),
                'body' => $errorBody,
                'headers' => $response->headers()
            ]);

            return [
                'status' => 'error',
                'message' => $errorBody['status_message'] ?? 'Failed to create transaction',
                'raw_response' => $errorBody,
                'http_status' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans Service Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle Callback from Midtrans
     */
    public function handleCallback(array $payload): array
    {
        try {
            $orderId = $payload['order_id'] ?? null;
            $transactionStatus = $payload['transaction_status'] ?? null;
            $statusCode = $payload['status_code'] ?? null;

            if (!$orderId || !$transactionStatus) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid callback payload'
                ];
            }

            // Mapping status Midtrans ke status internal
            $statusMap = [
                'capture' => 'success',
                'settlement' => 'success',
                'pending' => 'pending',
                'deny' => 'failed',
                'cancel' => 'failed',
                'expire' => 'expired',
                'failure' => 'failed'
            ];

            $paymentStatus = $statusMap[$transactionStatus] ?? 'pending';

            // Ambil payment method dari payload
            $paymentMethod = null;
            $paymentChannel = null;
            $bank = null;
            $vaNumber = null;

            if (isset($payload['payment_type'])) {
                $paymentMethod = $payload['payment_type'];
                
                if ($paymentMethod === 'bank_transfer') {
                    $paymentChannel = $payload['va_numbers'][0]['bank'] ?? null;
                    $vaNumber = $payload['va_numbers'][0]['va_number'] ?? null;
                    $bank = $paymentChannel;
                } elseif ($paymentMethod === 'credit_card') {
                    $bank = $payload['bank'] ?? null;
                }
            }

            return [
                'status' => 'success',
                'order_id' => $orderId,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'payment_channel' => $paymentChannel,
                'bank' => $bank,
                'va_number' => $vaNumber,
                'raw_response' => $payload
            ];

        } catch (\Exception $e) {
            Log::error('Callback handling error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify Callback Signature
     */
    public function verifyCallbackSignature(array $payload): bool
    {
        try {
            $orderId = $payload['order_id'] ?? null;
            $statusCode = $payload['status_code'] ?? null;
            $grossAmount = $payload['gross_amount'] ?? null;

            if (!$orderId || !$statusCode || !$grossAmount) {
                Log::warning('Missing required fields for signature verification', $payload);
                return false;
            }

            // Verify menggunakan hash
            $hash = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
            
            // Hanya verifikasi jika ada signature_key
            if (isset($payload['signature_key'])) {
                return $hash === $payload['signature_key'];
            }

            // Jika tidak ada signature_key, log warning tapi tetap proceed
            Log::warning('No signature_key in callback payload', $payload);
            return true;

        } catch (\Exception $e) {
            Log::error('Signature verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check Transaction Status
     */
    public function checkStatus(string $orderId): array
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->get($this->baseUrl . '/' . $orderId . '/status');

            if ($response->successful()) {
                $result = $response->json();
                $transactionStatus = $result['transaction_status'] ?? null;

                $statusMap = [
                    'capture' => 'success',
                    'settlement' => 'success',
                    'pending' => 'pending',
                    'deny' => 'failed',
                    'cancel' => 'failed',
                    'expire' => 'expired',
                    'failure' => 'failed'
                ];

                return [
                    'status' => 'success',
                    'payment_status' => $statusMap[$transactionStatus] ?? 'pending',
                    'raw_response' => $result
                ];
            }

            return [
                'status' => 'error',
                'message' => $response->json()['status_message'] ?? 'Failed to check status'
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Expire Transaction
     */
    public function expireTransaction(string $orderId): array
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post($this->baseUrl . '/' . $orderId . '/expire');

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'raw_response' => $response->json()
                ];
            }

            return [
                'status' => 'error',
                'message' => $response->json()['status_message'] ?? 'Failed to expire transaction'
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate Snap Token (alternative method)
     */
    public function generateSnapToken(array $params): ?string
    {
        $result = $this->createTransaction($params);
        return $result['status'] === 'success' ? $result['snap_token'] : null;
    }

    /**
     * Get Client Key for frontend
     */
    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    /**
     * Check if production mode
     */
    public function isProduction(): bool
    {
        return $this->isProduction;
    }
}