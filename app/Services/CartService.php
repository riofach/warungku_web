<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'cart';

    /**
     * Add item to cart
     *
     * @param string $itemId
     * @param int $quantity
     * @return void
     * @throws \Exception
     */
    public function add(string $itemId, int $quantity = 1): void
    {
        $cart = Session::get($this->sessionKey, []);
        
        // Find item to ensure it exists and check stock
        // Assuming Item model has 'id', 'name', 'sell_price', 'stock', 'image_url'
        $item = Item::findOrFail($itemId);

        // Check if item exists in cart
        if (isset($cart[$itemId])) {
            $newQuantity = $cart[$itemId]['quantity'] + $quantity;
        } else {
            $newQuantity = $quantity;
        }

        // Validate stock
        if ($newQuantity > $item->stock) {
            throw new \Exception('Stok tidak mencukupi');
        }

        // Update cart
        $cart[$itemId] = [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->sell_price,
            'quantity' => $newQuantity,
            'image_url' => $item->image_url,
            'stock_max' => $item->stock,
        ];

        Session::put($this->sessionKey, $cart);
    }

    /**
     * Get cart content
     *
     * @return array
     */
    public function get(): array
    {
        return Session::get($this->sessionKey, []);
    }

    /**
     * Get total cart value
     *
     * @return int
     */
    public function total(): int
    {
        $cart = $this->get();
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * Get total item count in cart
     *
     * @return int
     */
    public function count(): int
    {
        $cart = $this->get();
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Update item quantity
     *
     * @param string $itemId
     * @param int $quantity
     * @return void
     * @throws \Exception
     */
    public function update(string $itemId, int $quantity): void
    {
        $cart = Session::get($this->sessionKey, []);
        if (isset($cart[$itemId])) {
            if ($quantity <= 0) {
                unset($cart[$itemId]);
            } else {
                // Check stock
                 $item = Item::findOrFail($itemId);
                 if ($quantity > $item->stock) {
                     throw new \Exception('Stok tidak mencukupi');
                 }
                $cart[$itemId]['quantity'] = $quantity;
            }
            Session::put($this->sessionKey, $cart);
        }
    }

    /**
     * Remove item from cart
     *
     * @param string $itemId
     * @return void
     */
    public function remove(string $itemId): void
    {
        $cart = Session::get($this->sessionKey, []);
        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            Session::put($this->sessionKey, $cart);
        }
    }

    /**
     * Clear cart
     *
     * @return void
     */
    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }
}
