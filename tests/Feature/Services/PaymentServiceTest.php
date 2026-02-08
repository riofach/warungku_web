<?php

namespace Tests\Feature\Services;

use App\Models\HousingBlock;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test generatePayment populates order fields.
     */
    public function test_generate_payment_populates_order_fields(): void
    {
        // Arrange
        $housingBlock = HousingBlock::create(['name' => 'Blok A']);
        
        $order = Order::create([
            'code' => 'WRG-20260209-0001',
            'housing_block_id' => $housingBlock->id,
            'customer_name' => 'Somay',
            'payment_method' => 'qris',
            'delivery_type' => 'delivery',
            'status' => 'pending',
            'total' => 15000,
        ]);

        $service = new PaymentService();

        // Act
        $service->generatePayment($order);

        // Assert
        $this->assertNotNull($order->payment_url, 'Payment URL should be generated');
        $this->assertNotNull($order->payment_expires_at, 'Payment expiry should be set');
        
        // Assert mock implementation details (optional but good for verification)
        $this->assertStringContainsString('https://api.qrserver.com', $order->payment_url);
    }
}
