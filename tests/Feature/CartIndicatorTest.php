<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class CartIndicatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_count_is_globally_available_in_views()
    {
        // Add items to cart directly via session or using a service
        // But since we are testing view composer which relies on CartService,
        // we can use the service or session directly.
        // Let's use the route to add items to ensure end-to-end behavior
        
        $item = Item::factory()->create([
            'stock' => 10,
            'sell_price' => 5000
        ]);

        // Simulate adding to cart via API or session
        $cart = [
            $item->id => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->sell_price,
                'quantity' => 3,
                'image_url' => 'test.jpg',
                'stock_max' => 10,
            ]
        ];
        // Visit homepage (which should use the main layout)
        // Use withSession to ensure session data is available in the request
        $response = $this->withSession(['cart' => $cart])->get('/');

        // Assert view has cartCount variable
        // Note: assertViewHas checks if the variable is passed to the view
        $response->assertViewHas('cartCount', 3);
        
        // Also check if it's rendered in the HTML (optional, but good for integration)
        // We expect it to be used in Alpine.js init
        $response->assertSee('x-init="$store.cart.count = 3"', false);
    }

    public function test_cart_count_is_zero_when_empty()
    {
        // Session::forget('cart'); // Not needed if we don't set it in withSession

        $response = $this->get('/');

        $response->assertViewHas('cartCount', 0);
        $response->assertSee('x-init="$store.cart.count = 0"', false);
    }
}
