<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CartService
{
    const SESSION_KEY = 'warungku_cart';

    /**
     * Get all items in cart
     */
    public function getItems(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * Add item to cart
     */
    public function addItem(int $itemId, string $name, int $price, int $quantity = 1): void
    {
        $cart = $this->getItems();
        
        $existingIndex = $this->findItemIndex($cart, $itemId);
        
        if ($existingIndex !== null) {
            $cart[$existingIndex]['quantity'] += $quantity;
        } else {
            $cart[] = [
                'id' => $itemId,
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity,
            ];
        }
        
        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(int $itemId, int $quantity): void
    {
        $cart = $this->getItems();
        $index = $this->findItemIndex($cart, $itemId);
        
        if ($index !== null) {
            if ($quantity <= 0) {
                unset($cart[$index]);
                $cart = array_values($cart);
            } else {
                $cart[$index]['quantity'] = $quantity;
            }
            Session::put(self::SESSION_KEY, $cart);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $itemId): void
    {
        $cart = $this->getItems();
        $index = $this->findItemIndex($cart, $itemId);
        
        if ($index !== null) {
            unset($cart[$index]);
            Session::put(self::SESSION_KEY, array_values($cart));
        }
    }

    /**
     * Clear cart
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Get cart count
     */
    public function getCount(): int
    {
        return array_sum(array_column($this->getItems(), 'quantity'));
    }

    /**
     * Get cart total
     */
    public function getTotal(): int
    {
        $items = $this->getItems();
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return $total;
    }

    /**
     * Find item index in cart
     */
    private function findItemIndex(array $cart, int $itemId): ?int
    {
        foreach ($cart as $index => $item) {
            if ($item['id'] === $itemId) {
                return $index;
            }
        }
        return null;
    }
}
