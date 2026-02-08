<?php

namespace Tests\Feature;

use App\Models\HousingBlock;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_payment_page_generates_payment_if_missing(): void
    {
        $housingBlock = HousingBlock::create(['name' => 'Blok A']);
        $order = Order::create([
            'code' => 'WRG-TEST-001',
            'housing_block_id' => $housingBlock->id,
            'customer_name' => 'Somay',
            'payment_method' => 'qris',
            'delivery_type' => 'delivery',
            'status' => 'pending',
            'total' => 15000,
        ]);

        $response = $this->get("/payment/{$order->code}");

        $response->assertStatus(200);
        $response->assertViewIs('checkout.payment');
        $response->assertViewHas('order');

        $order->refresh();
        $this->assertNotNull($order->payment_url);
    }

    public function test_show_payment_redirects_if_not_pending(): void
    {
        $housingBlock = HousingBlock::create(['name' => 'Blok A']);
        $order = Order::create([
            'code' => 'WRG-TEST-002',
            'housing_block_id' => $housingBlock->id,
            'customer_name' => 'Somay',
            'payment_method' => 'qris',
            'delivery_type' => 'delivery',
            'status' => 'paid',
            'total' => 15000,
        ]);

        $response = $this->get("/payment/{$order->code}");

        $response->assertRedirect("/tracking/{$order->code}");
    }
    
    public function test_check_status_returns_json(): void
    {
        $housingBlock = HousingBlock::create(['name' => 'Blok A']);
        $order = Order::create([
            'code' => 'WRG-TEST-003',
            'housing_block_id' => $housingBlock->id,
            'customer_name' => 'Somay',
            'payment_method' => 'qris',
            'delivery_type' => 'delivery',
            'status' => 'pending',
            'total' => 15000,
        ]);
        
        $response = $this->getJson("/payment/{$order->code}/check");
        
        $response->assertStatus(200);
        $response->assertJson(['status' => 'pending']);
    }
}
