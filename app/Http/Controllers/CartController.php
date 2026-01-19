<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display cart
     */
    public function index()
    {
        $cartItems = $this->cartService->getItems();
        $total = $this->cartService->getTotal();

        return view('cart.index', compact('cartItems', 'total'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'integer|min:1',
        ]);

        $item = Item::findOrFail($request->item_id);
        $quantity = $request->quantity ?? 1;

        if ($item->stock < $quantity) {
            return back()->with('error', 'Stok tidak mencukupi.');
        }

        $this->cartService->addItem(
            $item->id,
            $item->name,
            $item->sell_price,
            $quantity
        );

        return back()->with('success', 'Ditambahkan ke keranjang');
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, int $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $this->cartService->updateQuantity($itemId, $request->quantity);

        return back()->with('success', 'Keranjang diperbarui');
    }

    /**
     * Remove item from cart
     */
    public function remove(int $itemId)
    {
        $this->cartService->removeItem($itemId);

        return back()->with('success', 'Item dihapus dari keranjang');
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        $this->cartService->clear();

        return redirect()->route('shop.index')->with('success', 'Keranjang dikosongkan');
    }
}
