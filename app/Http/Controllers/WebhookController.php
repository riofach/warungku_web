<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle incoming payment webhook from Duitku.
     * Note: Duitku sends data as POST Form Data (x-www-form-urlencoded), not JSON body.
     */
    public function handle(Request $request): JsonResponse
    {
        // Log incoming request for debugging (remove in production if sensitive)
        Log::info('Duitku Webhook Received:', $request->all());

        // 1. Verify Signature
        if (!$this->paymentService->verifySignature($request)) {
            Log::warning('Duitku Webhook: Invalid Signature', [
                'ip' => $request->ip(),
                'payload' => $request->all()
            ]);
            return response()->json(['error' => 'Invalid signature'], 400); // 400 Bad Request
        }

        // 2. Process Webhook
        try {
            $this->paymentService->handleWebhook($request);
            return response()->json(['status' => 'success'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Duitku Webhook: Order Not Found', ['order_code' => $request->input('merchantOrderId')]);
            return response()->json(['error' => 'Order not found'], 404);
        } catch (\Exception $e) {
            Log::error('Duitku Webhook: Server Error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}
