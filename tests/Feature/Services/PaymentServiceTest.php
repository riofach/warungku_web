<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Models\HousingBlock;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    /**
     * Test generatePayment populates order fields.
     */
    public function test_generate_payment_populates_order_fields(): void
    {
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

        $this->paymentService->generatePayment($order);

        $this->assertNotNull($order->payment_url, 'Payment URL should be generated');
        $this->assertNotNull($order->payment_expires_at, 'Payment expiry should be set');
        $this->assertStringContainsString('https://api.qrserver.com', $order->payment_url);
    }

    public function test_verify_signature_returns_true_for_valid_signature(): void
    {
        $request = Request::create('/webhook/payment', 'POST', [], [], [], [
            'HTTP_X_SIGNATURE' => 'valid-signature',
        ]);

        // Mock verification logic - assuming 'valid-signature' is correct
        // In real impl, we'd check config('services.payment.signature_key')
        // For this test, we expect the service to validate against 'valid-signature'
        $this->assertTrue($this->paymentService->verifySignature($request));
    }

    public function test_verify_signature_returns_false_for_invalid_signature(): void
    {
        $request = Request::create('/webhook/payment', 'POST', [], [], [], [
            'HTTP_X_SIGNATURE' => 'invalid-signature',
        ]);

        $this->assertFalse($this->paymentService->verifySignature($request));
    }

    public function test_handle_webhook_success_reduces_stock_and_updates_status(): void
    {
        // Arrange
        $housingBlock = HousingBlock::create(['name' => 'Blok B']);
        $category = Category::factory()->create();
        $item = Item::factory()->create(['category_id' => $category->id, 'stock' => 10]);

        $order = Order::create([
            'code' => 'WRG-20260209-0002',
            'housing_block_id' => $housingBlock->id,
            'customer_name' => 'Budi',
            'payment_method' => 'qris',
            'delivery_type' => 'pickup',
            'status' => 'pending',
            'total' => 24000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'price' => 12000,
            'subtotal' => 24000,
        ]);

        // Use Request object with input data
        $request = new Request();
        $request->merge([
            'order_code' => $order->code,
            'status' => 'success'
        ]);

        // Act
        $this->paymentService->handleWebhook($request);

        // Assert
        $this->assertEquals('paid', $order->refresh()->status);
        $this->assertEquals(8, $item->refresh()->stock); // 10 - 2
    }

    public function test_handle_webhook_failed_updates_status_only(): void
    {
        // Arrange
        $housingBlock = HousingBlock::create(['name' => 'Blok C']);
        $category = Category::factory()->create();
        $item = Item::factory()->create(['category_id' => $category->id, 'stock' => 10]);

        $order = Order::create([
            'code' => 'WRG-20260209-0003',
            'housing_block_id' => $housingBlock->id,
            'customer_name' => 'Cici',
            'payment_method' => 'qris',
            'delivery_type' => 'delivery',
            'status' => 'pending',
            'total' => 12000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'price' => 12000,
            'subtotal' => 12000,
        ]);

        $request = new Request();
        $request->merge([
            'order_code' => $order->code,
            'status' => 'failed'
        ]);

        // Act
        $this->paymentService->handleWebhook($request);

        // Assert
        $this->assertEquals('failed', $order->refresh()->status);
        $this->assertEquals(10, $item->refresh()->stock); // Unchanged
    }

    public function test_handle_webhook_idempotency_does_not_reduce_stock_twice(): void
    {
        // Arrange
        $housingBlock = HousingBlock::create(['name' => 'Blok D']);
        $category = Category::factory()->create();
        // Initial stock 8, meaning 2 already deducted from original 10?
        // Let's say stock is currently 8.
        $item = Item::factory()->create(['category_id' => $category->id, 'stock' => 8]);

        $order = Order::create([
            'code' => 'WRG-20260209-0004',
            'housing_block_id' => $housingBlock->id,
            'customer_name' => 'Dodi',
            'payment_method' => 'qris',
            'delivery_type' => 'pickup',
            'status' => 'paid', // Already paid
            'total' => 24000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'price' => 12000,
            'subtotal' => 24000,
        ]);

        $request = new Request();
        $request->merge([
            'order_code' => $order->code,
            'status' => 'success'
        ]);

        // Act
        $this->paymentService->handleWebhook($request);

        // Assert
        $this->assertEquals('paid', $order->refresh()->status);
        $this->assertEquals(8, $item->refresh()->stock); // Still 8, not 6
    }
}
