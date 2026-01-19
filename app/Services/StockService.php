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
     * Reduce stock for all items in an order
     * CRITICAL: This should ONLY be called after payment success!
     */
    public function reduceStockForOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $orderItem) {
                $item = Item::lockForUpdate()->find($orderItem->item_id);
                
                if (!$item) {
                    Log::error("StockService: Item not found", ['item_id' => $orderItem->item_id]);
                    throw new \Exception("Item not found: {$orderItem->item_id}");
                }
                
                if ($item->stock < $orderItem->quantity) {
                    Log::error("StockService: Insufficient stock", [
                        'item_id' => $item->id,
                        'available' => $item->stock,
                        'requested' => $orderItem->quantity,
                    ]);
                    throw new \Exception("Insufficient stock for: {$item->name}");
                }
                
                $item->stock -= $orderItem->quantity;
                $item->save();
                
                Log::info("StockService: Stock reduced", [
                    'item_id' => $item->id,
                    'quantity' => $orderItem->quantity,
                    'new_stock' => $item->stock,
                ]);
            }
            
            return true;
        });
    }

    /**
     * Check if all items in cart have sufficient stock
     */
    public function validateStock(array $cartItems): array
    {
        $errors = [];
        
        foreach ($cartItems as $cartItem) {
            $item = Item::find($cartItem['id']);
            
            if (!$item) {
                $errors[] = "Produk tidak ditemukan.";
                continue;
            }
            
            if (!$item->is_active) {
                $errors[] = "Produk '{$item->name}' sudah tidak tersedia.";
                continue;
            }
            
            if ($item->stock < $cartItem['quantity']) {
                $errors[] = "Stok {$item->name} tidak mencukupi. Tersedia: {$item->stock}";
            }
        }
        
        return $errors;
    }
}
