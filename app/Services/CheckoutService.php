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

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
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
    public function createOrder(
        string $customerName,
        ?string $housingBlockId,
        string $deliveryType,
        string $paymentMethod
    ): Order {
        return DB::transaction(function () use ($customerName, $housingBlockId, $deliveryType, $paymentMethod) {
            $cartItems = $this->cartService->get();
            $total = $this->cartService->total();

            // Create order
            $order = Order::create([
                'code' => Order::generateCode(),
                'housing_block_id' => $housingBlockId,
                'customer_name' => $customerName,
                'payment_method' => $paymentMethod,
                'delivery_type' => $deliveryType,
                'status' => Order::STATUS_PENDING,
                'total' => $total,
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                $subtotal = $cartItem['price'] * $cartItem['quantity'];
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $cartItem['id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $cartItem['price'],
                    'subtotal' => $subtotal,
                ]);
            }

            // Clear cart
            $this->cartService->clear();

            return $order;
        });
    }
}
