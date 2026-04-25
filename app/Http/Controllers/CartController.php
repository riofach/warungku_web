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

    public function index()
    {
        $cartItems = $this->cartService->get();
        $total     = $this->cartService->total();

        return view('cart.index', compact('cartItems', 'total'));
    }

    /** Add item to cart. Accepts optional item_unit_id for multi-unit items. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id'      => 'required|exists:items,id',
            'quantity'     => 'integer|min:1',
            'item_unit_id' => 'nullable|exists:item_units,id',
        ]);

        try {
            $this->cartService->add(
                $validated['item_id'],
                $validated['quantity'] ?? 1,
                $validated['item_unit_id'] ?? null,
            );

            return response()->json([
                'success'    => true,
                'message'    => 'Ditambahkan ke keranjang',
                'cart_count' => $this->cartService->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function add(Request $request)
    {
        $response = $this->store($request);

        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }

        $data = $response->getData();
        return $data->success
            ? back()->with('success', $data->message)
            : back()->with('error', $data->message);
    }

    /** Update quantity. $cartKey = item_id or "{item_id}_{unit_id}" */
    public function update(Request $request, string $cartKey)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $this->cartService->update($cartKey, $request->quantity);

            $cartItems    = $this->cartService->get();
            $cartTotal    = $this->cartService->total();
            $itemSubtotal = isset($cartItems[$cartKey])
                ? $cartItems[$cartKey]['price'] * $cartItems[$cartKey]['quantity']
                : 0;

            if ($request->wantsJson()) {
                return response()->json([
                    'success'                 => true,
                    'message'                 => 'Keranjang diperbarui',
                    'cart_count'              => $this->cartService->count(),
                    'item_subtotal'           => $itemSubtotal,
                    'item_subtotal_formatted' => 'Rp ' . number_format($itemSubtotal, 0, ',', '.'),
                    'cart_total'              => $cartTotal,
                    'cart_total_formatted'    => 'Rp ' . number_format($cartTotal, 0, ',', '.'),
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

    /** Remove item. $cartKey = item_id or "{item_id}_{unit_id}" */
    public function destroy(Request $request, string $cartKey)
    {
        $this->cartService->remove($cartKey);

        if ($request->wantsJson()) {
            $cartTotal = $this->cartService->total();
            return response()->json([
                'success'              => true,
                'message'              => 'Item dihapus',
                'cart_count'           => $this->cartService->count(),
                'cart_total'           => $cartTotal,
                'cart_total_formatted' => 'Rp ' . number_format($cartTotal, 0, ',', '.'),
            ]);
        }

        return back()->with('success', 'Item dihapus dari keranjang');
    }

    public function clear()
    {
        $this->cartService->clear();
        return redirect()->route('shop.index')->with('success', 'Keranjang dikosongkan');
    }

    public function count(): JsonResponse
    {
        return response()->json(['count' => $this->cartService->count()]);
    }
}
