<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display the payment page.
     */
    public function show(string $code): View|RedirectResponse
    {
        $order = Order::where('code', $code)->firstOrFail();

        // If order is not pending, redirect to tracking
        if ($order->status !== Order::STATUS_PENDING) {
            return redirect()->route('tracking.show', ['code' => $code]);
        }

        // Generate payment URL if missing
        if (empty($order->payment_url)) {
            $this->paymentService->generatePayment($order);
            $order->refresh();
        }

        return view('checkout.payment', compact('order'));
    }

    /**
     * Check order status via JSON (for polling).
     */
    public function check(string $code): JsonResponse
    {
        $order = Order::where('code', $code)->firstOrFail();

        return response()->json([
            'status' => $order->status,
        ]);
    }
}
