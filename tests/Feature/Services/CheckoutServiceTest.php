<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private CheckoutService $checkoutService;
    private $cartServiceMock;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock CartService
        $this->cartServiceMock = Mockery::mock(CartService::class);
        
        // Use real OrderService
        $this->orderService = new OrderService();
        
        $this->checkoutService = new CheckoutService(
            $this->cartServiceMock,
            $this->orderService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_order_uses_db_price_not_cart_price()
    {
        // Create category
        $category = Category::create(['name' => 'Food']);

        // Create item with DB price 2000
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Test Item',
            'buy_price' => 1000,
            'sell_price' => 2000,
            'stock' => 10,
            'is_active' => true
        ]);

        // Cart says price is 1000 (tampered)
        $cartItems = [
            [
                'id' => $item->id,
                'name' => 'Test Item',
                'quantity' => 2,
                'price' => 1000 // Fake price
            ]
        ];

        // Mock CartService behavior
        $this->cartServiceMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($cartItems);
        
        $this->cartServiceMock
            ->shouldReceive('total')
            ->never(); // Logic calculates total itself now
            
        $this->cartServiceMock
            ->shouldReceive('clear')
            ->once();

        // Checkout data
        $data = [
            'customer_name' => 'John Doe',
            'housing_block_id' => null,
            'payment_method' => 'cash',
            'delivery_type' => 'pickup'
        ];

        // Execute
        $order = $this->checkoutService->createOrder($data);

        // Assert
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'code' => $order->code,
            'status' => 'pending',
            'total' => 4000 // 2 * 2000 (DB price), NOT 2000 (cart price)
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'item_id' => $item->id,
            'price' => 2000,
            'quantity' => 2,
            'subtotal' => 4000
        ]);
        
        // Check formatted code
        $this->assertStringStartsWith('WRG-', $order->code);
    }
}
