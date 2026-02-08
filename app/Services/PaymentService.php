<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Generate payment for the order.
     */
    public function generatePayment(Order $order): void
    {
        // Mock implementation
        // Use a free QR code generator API for the MVP/Mock
        $order->payment_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . $order->code;
        
        // Set expiry to 15 minutes from now
        $order->payment_expires_at = Carbon::now()->addMinutes(15);
        
        $order->save();
    }
}
