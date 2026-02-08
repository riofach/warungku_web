<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    protected CartService $cartService;
    protected OrderService $orderService;

    public function __construct(CartService $cartService, OrderService $orderService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
    }

    /**
     * Validate checkout can proceed
     */
    public function validateCheckout(): array
    {
        $errors = [];

        // Check if warung is open
        if (!Setting::isWarungOpen()) {
            $errors[] = 'Warung sedang tutup. Silakan kembali pada jam operasional.';
        }

        // Check cart is not empty
        $cartItems = $this->cartService->get();
        if (empty($cartItems)) {
            $errors[] = 'Keranjang belanja kosong.';
        }

        // Check stock availability
        foreach ($cartItems as $cartItem) {
            $item = Item::find($cartItem['id']);
            if (!$item) {
                $errors[] = "Produk '{$cartItem['name']}' tidak ditemukan.";
            } elseif ($item->stock < $cartItem['quantity']) {
                $errors[] = "Stok {$item->name} tidak mencukupi. Tersedia: {$item->stock}";
            }
        }

        return $errors;
    }

    /**
     * Create order from cart
     * Note: Stock is NOT reduced here - only after payment success!
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $cartItems = $this->cartService->get();
            
            if (empty($cartItems)) {
                throw new \Exception('Keranjang belanja kosong.');
            }

            // 1. Generate Unique Code
            $code = $this->orderService->generateUniqueCode();

            // 2. Create Order Record
            $order = Order::create([
                'code' => $code,
                'housing_block_id' => $data['housing_block_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'payment_method' => $data['payment_method'],
                'delivery_type' => $data['delivery_type'] ?? 'pickup',
                'status' => Order::STATUS_PENDING,
                'total' => 0, // Will update after calculating items
            ]);

            $total = 0;

            // 3. Process Items with Security Check
            foreach ($cartItems as $cartItem) {
                // LOCK the item to ensure existence and price integrity
                // Also helps preventing race conditions if stock management changes later
                $query = Item::query();
                if (!app()->runningUnitTests()) {
                    $query->lockForUpdate();
                }
                $item = $query->find($cartItem['id']);

                if (!$item) {
                    throw new \Exception("Produk '{$cartItem['name']}' tidak ditemukan atau telah dihapus.");
                }

                // CRITICAL: Use DB price, NEVER trust client/session price
                $price = $item->sell_price;
                $quantity = $cartItem['quantity'];
                $subtotal = $price * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            // 4. Update Order Total
            $order->update(['total' => $total]);

            // 5. Clear Cart
            $this->cartService->clear();

            return $order;
        });
    }
}
