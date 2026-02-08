<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Services\CartService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StockValidationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_checkout_aborted_if_stock_insufficient(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        // 1. Create an item with stock 10
        $item = Item::create([
            'name' => 'Test Item',
            'buy_price' => 1000,
            'sell_price' => 2000,
            'stock' => 10,
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        // 2. Add 10 items to cart (this passes initial check)
        $cartService = app(CartService::class);
        $cartService->add($item->id, 10);

        // 3. Simulate stock reduction
        $item->update(['stock' => 5]);

        // 4. Submit checkout form
        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Test User',
            'housing_block_id' => null,
            'delivery_type' => 'pickup',
            'payment_method' => 'tunai',
        ]);

        // 5. Assert redirect back to cart with error
        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHasErrors();
    }

    public function test_checkout_proceeds_if_stock_sufficient(): void
    {
        $category = Category::create(['name' => 'Test Category 2']);

        // 1. Create an item with stock 10
        $item = Item::create([
            'name' => 'Test Item 2',
            'buy_price' => 1000,
            'sell_price' => 2000,
            'stock' => 10,
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        // 2. Add 5 items to cart
        $cartService = app(CartService::class);
        $cartService->add($item->id, 5);

        // 3. Submit checkout form
        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Test User',
            'housing_block_id' => null,
            'delivery_type' => 'pickup',
            'payment_method' => 'tunai',
        ]);

        // 4. Assert success
        $response->assertSessionHas('success');
    }
}
