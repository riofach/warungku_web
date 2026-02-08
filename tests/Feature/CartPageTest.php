<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Item;
use Illuminate\Support\Facades\Session;

class CartPageTest extends TestCase
{
    public function test_cart_page_can_be_accessed()
    {
        $response = $this->get('/cart');
        $response->assertStatus(200);
        $response->assertViewIs('cart.index');
    }

    public function test_cart_displays_items()
    {
        $item = Item::factory()->create([
            'name' => 'Test Item',
            'sell_price' => 10000,
            'stock' => 10
        ]);

        $cart = [
            $item->id => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->sell_price,
                'quantity' => 2,
                'image_url' => 'test.jpg',
                'stock_max' => 10,
            ]
        ];

        Session::put('cart', $cart);

        $response = $this->get('/cart');
        $response->assertSee('Test Item');
        // Check for raw value in JSON data since formatting is client-side
        $response->assertSee('20000'); 
    }

    public function test_update_quantity_via_ajax()
    {
        $item = Item::factory()->create([
            'sell_price' => 10000,
            'stock' => 10
        ]);

        $cart = [
            $item->id => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->sell_price,
                'quantity' => 1,
                'image_url' => 'test.jpg',
                'stock_max' => 10,
            ]
        ];
        Session::put('cart', $cart);

        $response = $this->patchJson("/cart/{$item->id}", [
            'quantity' => 5
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'item_subtotal' => 50000,
                'cart_total' => 50000
            ]);

        $this->assertEquals(5, Session::get("cart.{$item->id}.quantity"));
    }

    public function test_remove_item_via_ajax()
    {
        $item = Item::factory()->create();

        $cart = [
            $item->id => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => 10000,
                'quantity' => 1,
                'image_url' => 'test.jpg',
                'stock_max' => 10,
            ]
        ];
        Session::put('cart', $cart);

        $response = $this->deleteJson("/cart/{$item->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cart_count' => 0
            ]);

        $this->assertEmpty(Session::get('cart'));
    }

    public function test_empty_state_is_displayed()
    {
        Session::forget('cart');
        
        $response = $this->get('/cart');
        $response->assertSee('Keranjang kosong');
        $response->assertSee('Mulai Belanja');
    }
}
