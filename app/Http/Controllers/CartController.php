<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        $cartItems = $this->cartService->get();
        $total = $this->cartService->total();

        return view('cart.index', compact('cartItems', 'total'));
    }

    /**
     * Add item to cart (JSON API)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'integer|min:1',
        ]);

        try {
            $this->cartService->add(
                $validated['item_id'],
                $validated['quantity'] ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'Ditambahkan ke keranjang',
                'cart_count' => $this->cartService->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * Add item to cart (Form Submit - Legacy support if needed)
     */
    public function add(Request $request)
    {
        // For backwards compatibility, might redirect
        $response = $this->store($request);
        
        // If it returns JSON, we might want to decode it and redirect if not AJAX
        // But for now, let's just use store logic and return redirect if not ajax
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }
        
        $data = $response->getData();
        if ($data->success) {
            return back()->with('success', $data->message);
        } else {
            return back()->with('error', $data->message);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, string $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $this->cartService->update($itemId, $request->quantity);

            // Calculate new totals for JSON response
            $itemSubtotal = 0;
            $cartItems = $this->cartService->get();
            $cartTotal = $this->cartService->total();
            
            // Get subtotal for specific item
            if (isset($cartItems[$itemId])) {
                 $itemSubtotal = $cartItems[$itemId]['price'] * $cartItems[$itemId]['quantity'];
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Keranjang diperbarui',
                    'cart_count' => $this->cartService->count(),
                    'item_subtotal' => $itemSubtotal,
                    'item_subtotal_formatted' => 'Rp ' . number_format($itemSubtotal, 0, ',', '.'),
                    'cart_total' => $cartTotal,
                    'cart_total_formatted' => 'Rp ' . number_format($cartTotal, 0, ',', '.')
                ]);
            }

            return back()->with('success', 'Keranjang diperbarui');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, string $itemId)
    {
        $this->cartService->remove($itemId);

        if ($request->wantsJson()) {
             // Calculate new totals
            $cartTotal = $this->cartService->total();

            return response()->json([
                'success' => true, 
                'message' => 'Item dihapus',
                'cart_count' => $this->cartService->count(),
                'cart_total' => $cartTotal,
                'cart_total_formatted' => 'Rp ' . number_format($cartTotal, 0, ',', '.')
            ]);
        }

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
    
    /**
     * Get cart count (JSON API)
     */
    public function count(): JsonResponse
    {
        return response()->json([
            'count' => $this->cartService->count(),
        ]);
    }
}
