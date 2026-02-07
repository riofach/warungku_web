<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Item;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
    }

    public function test_can_add_item_to_cart()
    {
        $item = Item::factory()->create([
            'stock' => 10,
            'sell_price' => 5000
        ]);

        $this->cartService->add($item->id, 1);

        $cart = Session::get('cart');
        
        $this->assertArrayHasKey($item->id, $cart);
        $this->assertEquals(1, $cart[$item->id]['quantity']);
        $this->assertEquals($item->name, $cart[$item->id]['name']);
        $this->assertEquals(5000, $cart[$item->id]['price']);
    }

    public function test_can_add_multiple_quantity()
    {
        $item = Item::factory()->create(['stock' => 10]);

        $this->cartService->add($item->id, 2);

        $cart = Session::get('cart');
        $this->assertEquals(2, $cart[$item->id]['quantity']);
    }

    public function test_adding_existing_item_increments_quantity()
    {
        $item = Item::factory()->create(['stock' => 10]);

        $this->cartService->add($item->id, 1);
        $this->cartService->add($item->id, 2);

        $cart = Session::get('cart');
        $this->assertEquals(3, $cart[$item->id]['quantity']);
    }

    public function test_cannot_add_more_than_stock()
    {
        $item = Item::factory()->create(['stock' => 5]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stok tidak mencukupi');

        $this->cartService->add($item->id, 6);
    }

    public function test_cannot_add_more_than_stock_incrementally()
    {
        $item = Item::factory()->create(['stock' => 5]);

        $this->cartService->add($item->id, 3);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stok tidak mencukupi');
        
        $this->cartService->add($item->id, 3); // Total would be 6
    }

    public function test_count_returns_total_items()
    {
        $item1 = Item::factory()->create(['stock' => 10]);
        $item2 = Item::factory()->create(['stock' => 10]);

        $this->cartService->add($item1->id, 2);
        $this->cartService->add($item2->id, 3);

        $this->assertEquals(5, $this->cartService->count());
    }

    public function test_get_returns_cart_content()
    {
        $item = Item::factory()->create(['stock' => 10]);
        $this->cartService->add($item->id, 1);

        $cart = $this->cartService->get();
        
        $this->assertIsArray($cart);
        $this->assertArrayHasKey($item->id, $cart);
    }
}
