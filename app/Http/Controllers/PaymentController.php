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
            $success = $this->paymentService->generatePayment($order);

            if ($success) {
                $order->refresh();
            } else {
                // API failed — show error but still render the page
                // The view handles null payment_url gracefully
                session()->flash('payment_error', 'Gagal menghubungi payment gateway. Silakan refresh halaman atau coba beberapa saat lagi.');
                $order->refresh();
            }
        }

        return view('checkout.payment', compact('order'));
    }

    /**
     * Check order status via JSON (for polling).
     * Also verifies payment status directly with Duitku API as webhook fallback.
     */
    public function check(string $code): JsonResponse
    {
        $order = Order::where('code', $code)->firstOrFail();

        // Fallback: If order is still pending, check Duitku API directly
        if ($order->status === Order::STATUS_PENDING) {
            $isPaid = $this->paymentService->checkPaymentStatus($order);
            if ($isPaid) {
                // Process payment success (idempotent - safe to call multiple times)
                $this->paymentService->processPaymentSuccess($order);
                $order->refresh(); // Reload from DB to get updated status
            }
        }

        return response()->json([
            'status' => $order->status,
        ]);
    }
}
