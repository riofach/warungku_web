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
        $this->cartService  = $cartService;
        $this->orderService = $orderService;
    }

    public function validateCheckout(): array
    {
        $errors = [];

        if (!Setting::isWarungOpen()) {
            $errors[] = 'Warung sedang tutup. Silakan kembali pada jam operasional.';
        }

        $cartItems = $this->cartService->get();
        if (empty($cartItems)) {
            $errors[] = 'Keranjang belanja kosong.';
        }

        foreach ($cartItems as $cartItem) {
            $item = Item::find($cartItem['id']);
            if (!$item) {
                $errors[] = "Produk '{$cartItem['name']}' tidak ditemukan.";
                continue;
            }

            $needed = $cartItem['quantity'] * ($cartItem['quantity_base_used'] ?? 1);
            if ($item->stock < $needed) {
                $label = $cartItem['unit_label'] ?? '';
                $errors[] = "Stok {$item->name}" . ($label ? " ({$label})" : '') . " tidak mencukupi.";
            }
        }

        return $errors;
    }

    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $cartItems = $this->cartService->get();

            if (empty($cartItems)) {
                throw new \Exception('Keranjang belanja kosong.');
            }

            $code = $this->orderService->generateUniqueCode();

            $blockAddress = null;
            if (!empty($data['block_number']) && !empty($data['house_number'])) {
                $blockAddress = 'U' . $data['block_number'] . '/' . $data['house_number'];
            }

            $order = Order::create([
                'code'           => $code,
                'block_address'  => $blockAddress,
                'customer_name'  => $data['customer_name'],
                'whatsapp_number'=> $data['whatsapp_number'],
                'payment_method' => $data['payment_method'],
                'delivery_type'  => $data['delivery_type'] ?? 'pickup',
                'status'         => Order::STATUS_PENDING,
                'total'          => 0,
            ]);

            $total = 0;

            foreach ($cartItems as $cartItem) {
                $query = Item::query();
                if (!app()->runningUnitTests()) {
                    $query->lockForUpdate();
                }
                $item = $query->find($cartItem['id']);

                if (!$item) {
                    throw new \Exception("Produk '{$cartItem['name']}' tidak ditemukan atau telah dihapus.");
                }

                $itemUnitId       = $cartItem['item_unit_id'] ?? null;
                $quantityBaseUsed = $cartItem['quantity_base_used'] ?? 1;
                $quantity         = $cartItem['quantity'];

                // Use DB price — NEVER trust session price
                if ($itemUnitId) {
                    $unit  = $item->activeUnits()->where('id', $itemUnitId)->first();
                    if (!$unit) {
                        throw new \Exception("Satuan untuk '{$item->name}' tidak ditemukan.");
                    }
                    $price    = $unit->sell_price;
                    $buyPrice = $unit->buy_price;
                } else {
                    $price    = $item->sell_price;
                    $buyPrice = $item->buy_price;
                }

                $subtotal = $price * $quantity;

                OrderItem::create([
                    'order_id'           => $order->id,
                    'item_id'            => $item->id,
                    'item_unit_id'       => $itemUnitId,
                    'quantity'           => $quantity,
                    'quantity_base_used' => $quantityBaseUsed,
                    'price'              => $price,
                    'buy_price'          => $buyPrice,
                    'subtotal'           => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update(['total' => $total]);
            $this->cartService->clear();

            return $order;
        });
    }
}
