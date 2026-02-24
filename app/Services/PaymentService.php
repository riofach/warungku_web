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
                'lastName' => $order->housingBlock ? $order->housingBlock->name : '',
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

        $order = Order::where('code', $merchantOrderId)->firstOrFail();

        if ($order->status === 'paid') {
            return;
        }

        if ($resultCode === '00') {
            DB::transaction(function () use ($order) {
                $order->update(['status' => 'paid']);

                foreach ($order->orderItems as $orderItem) {
                    $item = $orderItem->item;
                    if ($item) {
                        $item->decrement('stock', $orderItem->quantity);
                    }
                }
            });
        } elseif ($resultCode === '02') {
            $order->update(['status' => 'failed']);
        }
    }
}
