<?php

namespace Tests\Feature;

use App\Models\HousingBlock;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup default Duitku Config for testing
        Config::set('services.duitku.merchant_code', 'TEST-MERCHANT');
        Config::set('services.duitku.api_key', 'TEST-API-KEY');
    }

    /**
     * Helper to generate Duitku MD5 signature
     */
    private function generateSignature($merchantCode, $amount, $merchantOrderId, $apiKey)
    {
        return md5($merchantCode . $amount . $merchantOrderId . $apiKey);
    }

    public function test_webhook_route_exists_and_bypasses_csrf_protection(): void
    {
        // Should return 400 Bad Request (Invalid Signature) instead of 419 CSRF or 404 Not Found
        $response = $this->post('/webhook/payment', []);
        $response->assertStatus(400); 
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $merchantCode = 'TEST-MERCHANT';
        $amount = 10000;
        $orderId = 'WRG-TEST-001';
        $apiKey = 'TEST-API-KEY';
        
        $invalidSignature = 'invalid-hash-123';

        $payload = [
            'merchantCode' => $merchantCode,
            'amount' => $amount,
            'merchantOrderId' => $orderId,
            'signature' => $invalidSignature,
        ];

        $response = $this->post('/webhook/payment', $payload);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid signature']);
    }

    public function test_webhook_processes_valid_payment_success(): void
    {
        // Arrange
        $housingBlock = HousingBlock::create(['name' => 'Blok A']);
        $item = Item::factory()->create(['stock' => 10]);
        
        $order = Order::create([
            'code' => 'WRG-20260209-0010',
            'housing_block_id' => $housingBlock->id,
            'customer_name' => 'Tester',
            'payment_method' => 'qris',
            'delivery_type' => 'pickup',
            'status' => 'pending',
            'total' => 10000,
        ]);
        
        OrderItem::create([
            'order_id' => $order->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'price' => 10000,
            'subtotal' => 10000,
        ]);

        $signature = $this->generateSignature('TEST-MERCHANT', 10000, $order->code, 'TEST-API-KEY');

        $payload = [
            'merchantCode' => 'TEST-MERCHANT',
            'amount' => 10000,
            'merchantOrderId' => $order->code,
            'signature' => $signature,
            'resultCode' => '00', // Success
            'reference' => 'REF-123',
        ];

        // Act
        $response = $this->post('/webhook/payment', $payload);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals('paid', $order->refresh()->status);
        $this->assertEquals(9, $item->refresh()->stock);
    }

    public function test_webhook_handles_payment_failure(): void
    {
        // Arrange
        $housingBlock = HousingBlock::create(['name' => 'Blok F']);
        $item = Item::factory()->create(['stock' => 10]);
        $order = Order::create([
            'code' => 'WRG-20260209-0013',
            'housing_block_id' => $housingBlock->id,
            'customer_name' => 'Fail',
            'payment_method' => 'qris',
            'delivery_type' => 'pickup',
            'status' => 'pending',
            'total' => 10000,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'price' => 5000,
            'subtotal' => 10000,
        ]);

        $signature = $this->generateSignature('TEST-MERCHANT', 10000, $order->code, 'TEST-API-KEY');

        $payload = [
            'merchantCode' => 'TEST-MERCHANT',
            'amount' => 10000,
            'merchantOrderId' => $order->code,
            'signature' => $signature,
            'resultCode' => '02', // Failed
            'reference' => 'REF-FAIL',
        ];

        // Act
        $response = $this->post('/webhook/payment', $payload);

        // Assert
        $response->assertStatus(200); // Duitku expects 200 even on logical failure handling
        $this->assertEquals('failed', $order->refresh()->status);
        $this->assertEquals(10, $item->refresh()->stock); // Stock Unchanged
    }
}
