<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Single source of truth: process a successful payment.
     * Update order status to 'paid' and reduce stock atomically.
     * Safe to call multiple times (idempotent - checks if already paid).
     */
    public function processPaymentSuccess(Order $order): void
    {
        // Idempotent: skip if already processed
        if ($order->status === 'paid' || $order->status === 'processing' ||
            $order->status === 'ready' || $order->status === 'completed') {
            Log::info('PaymentService: Order already processed, skipping.', ['code' => $order->code, 'status' => $order->status]);
            return;
        }

        DB::transaction(function () use ($order) {
            // 1. Update order status
            $order->update(['status' => 'paid']);

            // 2. Reduce stock via Supabase RPC function (more robust than Eloquent)
            try {
                DB::select('SELECT reduce_stock_for_order(?::uuid)', [$order->id]);
                Log::info('PaymentService: Stock reduced via RPC for order', ['code' => $order->code]);
            } catch (\Exception $rpcException) {
                // Fallback: reduce stock via Eloquent ORM if RPC fails
                Log::warning('PaymentService: RPC failed, falling back to Eloquent.', ['error' => $rpcException->getMessage()]);
                foreach ($order->orderItems as $orderItem) {
                    $item = $orderItem->item;
                    if ($item) {
                        $item->decrement('stock', $orderItem->quantity);
                        Log::info('PaymentService: Stock decremented via Eloquent', [
                            'item_id' => $item->id,
                            'quantity' => $orderItem->quantity,
                        ]);
                    } else {
                        Log::error('PaymentService: Item not found for orderItem', ['order_item_id' => $orderItem->id]);
                    }
                }
            }
        });
    }

    /**
     * Check payment status from Duitku API directly.
     * Returns true if payment is confirmed paid, false otherwise.
     */
    public function checkPaymentStatus(Order $order): bool
    {
        $merchantCode = config('services.duitku.merchant_code');
        $apiKey = config('services.duitku.api_key');
        $sandboxMode = config('services.duitku.sandbox_mode', true);

        $timestamp = round(microtime(true) * 1000);
        $signature = hash('sha256', $merchantCode . $timestamp . $apiKey);

        $url = $sandboxMode
            ? 'https://api-sandbox.duitku.com/api/merchant/transactionStatus'
            : 'https://api-prod.duitku.com/api/merchant/transactionStatus';

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-duitku-signature' => $signature,
                'x-duitku-timestamp' => $timestamp,
                'x-duitku-merchantcode' => $merchantCode,
            ])->post($url, [
                'merchantCode' => $merchantCode,
                'merchantOrderId' => $order->code,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Duitku API Status Check:', ['code' => $order->code, 'result' => $result]);

                // Duitku status code '00' = paid successfully
                $statusCode = $result['statusCode'] ?? $result['resultCode'] ?? null;
                if ($statusCode === '00') {
                    return true;
                }
            } else {
                Log::warning('Duitku API Status Check Failed', [
                    'code' => $order->code,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Duitku Status Check Exception: ' . $e->getMessage(), ['code' => $order->code]);
        }

        return false;
    }

    /**
     * Generate payment for the order (Integrate Duitku Create Invoice API).
     *
     * @return bool true if payment was generated successfully, false otherwise
     */
    public function generatePayment(Order $order): bool
    {
        $merchantCode = config('services.duitku.merchant_code');
        $apiKey = config('services.duitku.api_key');
        $sandboxMode = config('services.duitku.sandbox_mode', true);

        $paymentAmount = $order->total;
        $merchantOrderId = $order->code;
        $productDetails = 'Pembayaran Pesanan #' . $order->code;
        $email = 'customer@example.com';
        $phoneNumber = $order->whatsapp_number;

        $timestamp = round(microtime(true) * 1000);
        $signature = hash('sha256', $merchantCode . $timestamp . $apiKey);

        $params = [
            'paymentAmount' => $paymentAmount,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'merchantUserInfo' => $order->customer_name,
            'customerVaName' => $order->customer_name,
            'customerDetail' => [
                'firstName' => $order->customer_name,
                'lastName' => $order->block_address ?? '',
                'email' => $email,
                'phoneNumber' => $phoneNumber,
            ],
            'callbackUrl' => route('webhook.payment'),
            'returnUrl' => route('payment.show', $order->code),
            'expiryPeriod' => 60,
        ];

        $url = $sandboxMode
            ? 'https://api-sandbox.duitku.com/api/merchant/createInvoice'
            : 'https://api-prod.duitku.com/api/merchant/createInvoice';

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-duitku-signature' => $signature,
                'x-duitku-timestamp' => $timestamp,
                'x-duitku-merchantcode' => $merchantCode,
            ])->post($url, $params);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['paymentUrl'])) {
                    $order->payment_url = $result['paymentUrl'];
                    $order->payment_token = $result['reference'] ?? null;
                    $order->payment_expires_at = Carbon::now()->addMinutes(60);
                    $order->save();
                    return true;
                } else {
                    $errorMsg = $result['Message'] ?? $result['message'] ?? json_encode($result);
                    Log::error('Duitku API Error (No URL): ' . $errorMsg);
                }
            } else {
                $body = $response->json();
                $errorMsg = $body['Message'] ?? $body['message'] ?? $response->body();
                Log::error('Duitku API Request Failed: ' . $errorMsg);
            }
        } catch (\Exception $e) {
            Log::error('Duitku Exception: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Verify webhook signature (Callback Verification).
     */
    public function verifySignature(Request $request): bool
    {
        $merchantCode = config('services.duitku.merchant_code');
        $apiKey = config('services.duitku.api_key');

        $incomingMerchantCode = $request->input('merchantCode');
        $amount = $request->input('amount');
        $merchantOrderId = $request->input('merchantOrderId');
        $signature = $request->input('signature');

        if ($incomingMerchantCode !== $merchantCode) {
            return false;
        }

        $params = $merchantCode . $amount . $merchantOrderId . $apiKey;
        $calcSignature = md5($params);

        return $signature === $calcSignature;
    }

    /**
     * Handle payment webhook.
     */
    public function handleWebhook(Request $request): void
    {
        $merchantOrderId = $request->input('merchantOrderId');
        $resultCode = $request->input('resultCode');

        Log::info('Duitku Webhook Processing:', ['merchantOrderId' => $merchantOrderId, 'resultCode' => $resultCode]);

        $order = Order::where('code', $merchantOrderId)->firstOrFail();

        if ($resultCode === '00') {
            $this->processPaymentSuccess($order);
        } elseif ($resultCode === '02') {
            $order->update(['status' => 'failed']);
        }
    }
}
