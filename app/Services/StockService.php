<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Reduce stock for all items in an order.
     * For has_units items: reduces stock by quantity * quantity_base_used.
     * CRITICAL: Only call after payment success!
     */
    public function reduceStockForOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $orderItem) {
                $item = Item::lockForUpdate()->find($orderItem->item_id);

                if (!$item) {
                    Log::error('StockService: Item not found', ['item_id' => $orderItem->item_id]);
                    throw new \Exception("Item not found: {$orderItem->item_id}");
                }

                $quantityBaseUsed = $orderItem->quantity_base_used ?? 1;
                $stockToReduce    = $orderItem->quantity * $quantityBaseUsed;

                if ($item->stock < $stockToReduce) {
                    Log::error('StockService: Insufficient stock', [
                        'item_id'   => $item->id,
                        'available' => $item->stock,
                        'requested' => $stockToReduce,
                    ]);
                    throw new \Exception("Insufficient stock for: {$item->name}");
                }

                $item->stock -= $stockToReduce;
                $item->save();

                Log::info('StockService: Stock reduced', [
                    'item_id'           => $item->id,
                    'quantity'          => $orderItem->quantity,
                    'quantity_base_used'=> $quantityBaseUsed,
                    'stock_reduced'     => $stockToReduce,
                    'new_stock'         => $item->stock,
                ]);
            }

            return true;
        });
    }

    public function validateCartAvailability(array $cartItems): array
    {
        $errors = [];

        foreach ($cartItems as $cartItem) {
            $item = Item::find($cartItem['id']);

            if (!$item) {
                $errors[] = 'Produk tidak ditemukan.';
                continue;
            }

            if (!$item->is_active) {
                $errors[] = "Produk '{$item->name}' sudah tidak tersedia.";
                continue;
            }

            $quantityBaseUsed = $cartItem['quantity_base_used'] ?? 1;
            $needed           = $cartItem['quantity'] * $quantityBaseUsed;

            if ($item->stock < $needed) {
                $label = $cartItem['unit_label'] ?? '';
                $errors[] = "Stok {$item->name}" . ($label ? " ({$label})" : '') . " tidak mencukupi.";
            }
        }

        return $errors;
    }
}
