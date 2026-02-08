<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set test now to be within operating hours (e.g. 10:00)
        Carbon::setTestNow('2026-01-28 10:00:00');

        // Ensure store is open in settings
        Setting::updateOrCreate(
            ['key' => Setting::KEY_OPERATING_HOURS_OPEN],
            ['value' => '08:00']
        );
        Setting::updateOrCreate(
            ['key' => Setting::KEY_OPERATING_HOURS_CLOSE],
            ['value' => '21:00']
        );
        // Default isWarungOpen checks these values and current time
    }

    public function test_checkout_tunai_success()
    {
        // Setup Item
        $category = Category::create(['name' => 'Food']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'buy_price' => 10000,
            'sell_price' => 15000,
            'stock' => 100,
            'is_active' => true
        ]);

        // Seed Cart Session
        $cart = [
            $item->id => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->sell_price,
                'quantity' => 2,
                'image_url' => null,
                'stock_max' => $item->stock
            ]
        ];
        session(['cart' => $cart]);

        // Submit Checkout
        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Budi',
            'delivery_type' => 'pickup',
            'payment_method' => 'tunai'
        ]);

        // Assert
        $order = \App\Models\Order::where('customer_name', 'Budi')->first();
        $this->assertNotNull($order);
        $this->assertEquals('Budi', $order->customer_name);
        $this->assertEquals(30000, $order->total); // 15000 * 2
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('tunai', $order->payment_method);
        
        // Redirect to tracking
        $response->assertRedirect(route('tracking.show', ['code' => $order->code]));
        
        // Cart Cleared
        $this->assertEmpty(session('cart'));
    }

    public function test_checkout_qris_success()
    {
        // Setup Housing Block
        $block = \App\Models\HousingBlock::create(['name' => 'Blok A']);

        // Setup Item
        $category = Category::create(['name' => 'Drink']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Es Teh',
            'buy_price' => 2000,
            'sell_price' => 5000,
            'stock' => 50,
            'is_active' => true
        ]);

        // Seed Cart Session
        $cart = [
            $item->id => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->sell_price,
                'quantity' => 1,
                'image_url' => null,
                'stock_max' => $item->stock
            ]
        ];
        session(['cart' => $cart]);

        // Submit Checkout
        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Siti',
            'delivery_type' => 'delivery',
            'payment_method' => 'qris',
            'housing_block_id' => $block->id
        ]);

        // Assert
        $order = \App\Models\Order::where('customer_name', 'Siti')->first();
        $this->assertNotNull($order, 'Order was not created');
        $this->assertEquals('Siti', $order->customer_name);
        $this->assertEquals('qris', $order->payment_method);
        
        // Redirect to Payment Page
        $response->assertRedirect(route('payment.show', ['code' => $order->code]));
    }
}
