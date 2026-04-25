<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'cart';

    /**
     * Add item to cart.
     * For has_units items, item_unit_id is required.
     * Cart key = "{item_id}" for regular, "{item_id}_{unit_id}" for unit items.
     */
    public function add(string $itemId, int $quantity = 1, ?string $itemUnitId = null): void
    {
        $cart = Session::get($this->sessionKey, []);

        $item = Item::with('activeUnits')->findOrFail($itemId);

        if ($item->has_units) {
            if (!$itemUnitId) {
                throw new \Exception('Pilih satuan terlebih dahulu');
            }

            $unit = $item->activeUnits->firstWhere('id', $itemUnitId);
            if (!$unit) {
                throw new \Exception('Satuan tidak valid');
            }

            $cartKey   = "{$itemId}_{$itemUnitId}";
            $maxAvail  = $item->availableForUnit($unit->quantity_base);
            $newQty    = ($cart[$cartKey]['quantity'] ?? 0) + $quantity;

            if ($newQty > $maxAvail) {
                throw new \Exception("Stok tidak mencukupi. Tersedia: {$maxAvail} {$unit->label}");
            }

            $cart[$cartKey] = [
                'cart_key'           => $cartKey,
                'id'                 => $item->id,
                'name'               => $item->name,
                'price'              => $unit->sell_price,
                'buy_price'          => $unit->buy_price,
                'quantity'           => $newQty,
                'image_url'          => $item->image_url,
                'stock_max'          => $maxAvail,
                'item_unit_id'       => $unit->id,
                'unit_label'         => $unit->label,
                'quantity_base_used' => $unit->quantity_base,
            ];
        } else {
            $cartKey = $itemId;
            $newQty  = ($cart[$cartKey]['quantity'] ?? 0) + $quantity;

            if ($newQty > $item->stock) {
                throw new \Exception('Stok tidak mencukupi');
            }

            $cart[$cartKey] = [
                'cart_key'           => $cartKey,
                'id'                 => $item->id,
                'name'               => $item->name,
                'price'              => $item->sell_price,
                'buy_price'          => $item->buy_price,
                'quantity'           => $newQty,
                'image_url'          => $item->image_url,
                'stock_max'          => $item->stock,
                'item_unit_id'       => null,
                'unit_label'         => null,
                'quantity_base_used' => 1,
            ];
        }

        Session::put($this->sessionKey, $cart);
    }

    public function get(): array
    {
        return Session::get($this->sessionKey, []);
    }

    public function total(): int
    {
        $total = 0;
        foreach ($this->get() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function count(): int
    {
        $count = 0;
        foreach ($this->get() as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /** $cartKey = item_id or "{item_id}_{unit_id}" */
    public function update(string $cartKey, int $quantity): void
    {
        $cart = Session::get($this->sessionKey, []);

        if (!isset($cart[$cartKey])) return;

        if ($quantity <= 0) {
            unset($cart[$cartKey]);
            Session::put($this->sessionKey, $cart);
            return;
        }

        $cartItem = $cart[$cartKey];
        $item     = Item::findOrFail($cartItem['id']);

        if ($cartItem['item_unit_id']) {
            $unit     = ItemUnit::findOrFail($cartItem['item_unit_id']);
            $maxAvail = $item->availableForUnit($unit->quantity_base);
            if ($quantity > $maxAvail) {
                throw new \Exception("Stok tidak mencukupi. Tersedia: {$maxAvail} {$unit->label}");
            }
            $cart[$cartKey]['stock_max'] = $maxAvail;
        } else {
            if ($quantity > $item->stock) {
                throw new \Exception('Stok tidak mencukupi');
            }
            $cart[$cartKey]['stock_max'] = $item->stock;
        }

        $cart[$cartKey]['quantity'] = $quantity;
        Session::put($this->sessionKey, $cart);
    }

    public function remove(string $cartKey): void
    {
        $cart = Session::get($this->sessionKey, []);
        unset($cart[$cartKey]);
        Session::put($this->sessionKey, $cart);
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }
}
