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
     * Generate payment for the order (Integrate Duitku Create Invoice API).
     */
    public function generatePayment(Order $order): void
    {
        $merchantCode = config('services.duitku.merchant_code');
        $apiKey = config('services.duitku.api_key');
        $sandboxMode = config('services.duitku.sandbox_mode', true);

        $paymentAmount = $order->total;
        $merchantOrderId = $order->code; // Use our order code as Merchant Order ID
        $productDetails = 'Pembayaran Pesanan #' . $order->code;
        $email = 'customer@example.com'; // Default or retrieve from user if authenticated
        $phoneNumber = $order->whatsapp_number;

        // Construct Signature for Request: SHA256(merchantCode + timestamp + apiKey)
        $timestamp = round(microtime(true) * 1000); // Unix timestamp in ms
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
                'firstName' => $order->customer_name, // Nama Lengkap
                'lastName' => $order->housingBlock ? $order->housingBlock->name : '', // Blok Rumah
                'email' => $email,
                'phoneNumber' => $phoneNumber,
            ],
            'callbackUrl' => route('webhook.payment'), // Ensure this is publicly accessible via Ngrok
            'returnUrl' => route('payment.show', $order->code), // Redirect back to our payment page
            'expiryPeriod' => 60, // 60 minutes
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
                    $order->payment_token = $result['reference'] ?? null; // Store Duitku Reference
                    $order->payment_expires_at = Carbon::now()->addMinutes(60);
                    $order->save();
                } else {
                    Log::error('Duitku API Error (No URL): ' . json_encode($result));
                }
            } else {
                Log::error('Duitku API Request Failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Duitku Exception: ' . $e->getMessage());
        }
    }

    /**
     * Verify webhook signature (Callback Verification).
     */
    public function verifySignature(Request $request): bool
    {
        $merchantCode = config('services.duitku.merchant_code');
        $apiKey = config('services.duitku.api_key');

        // Extract parameters from callback
        // Duitku sends as POST form data (x-www-form-urlencoded) usually
        $incomingMerchantCode = $request->input('merchantCode');
        $amount = $request->input('amount');
        $merchantOrderId = $request->input('merchantOrderId');
        $signature = $request->input('signature');

        // Verify Merchant Code matches
        if ($incomingMerchantCode !== $merchantCode) {
            return false;
        }

        // Calculate Signature: MD5(merchantCode + amount + merchantOrderId + apiKey)
        // Ensure amount is string/integer without decimals if Duitku sends it that way. 
        // Docs example: '150000' (integer in string).

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
        $resultCode = $request->input('resultCode'); // '00' for Success, '01' for Pending/Failed

        // Find order by code (merchantOrderId)
        $order = Order::where('code', $merchantOrderId)->firstOrFail();

        // Idempotency check
        if ($order->status === 'paid') {
            return;
        }

        // Success Condition: resultCode == '00'
        if ($resultCode === '00') {
            DB::transaction(function () use ($order) {
                // Update order status
                $order->update(['status' => 'paid']);

                // Reduce stock
                foreach ($order->orderItems as $orderItem) {
                    $item = $orderItem->item;
                    if ($item) {
                        $item->decrement('stock', $orderItem->quantity);
                    }
                }
            });
        } elseif ($resultCode === '01' || $resultCode === '02') {
            // 01 is pending/process usually, but sometimes treated as failed depending on flow.
            // Docs: 02 = Failed.
            // We'll mark failed for 02.
            if ($resultCode === '02') {
                $order->update(['status' => 'failed']);
            }
        }
    }
}
