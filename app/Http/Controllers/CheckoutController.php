<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\HousingBlock;
use App\Models\Item;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\StockService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CheckoutService $checkoutService,
        protected StockService $stockService
    ) {}

    /**
     * Display checkout form
     */
    public function index()
    {
        $cartItems = $this->cartService->get();
        
        if (empty($cartItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja kosong');
        }

        // Validate stock availability
        // We can use StockService here too for consistency, but keep original logic for now as it handles removal of inactive items
        foreach ($cartItems as $key => $cartItem) {
             $freshItem = Item::find($cartItem['id']);
             
             if (!$freshItem || !$freshItem->is_active) {
                 $this->cartService->remove($key);
                 return redirect()->route('cart.index')
                     ->with('error', "Item {$cartItem['name']} tidak lagi tersedia.");
             }

             if ($freshItem->stock < $cartItem['quantity']) {
                 return redirect()->route('cart.index')
                     ->with('error', "Stok untuk {$freshItem->name} tidak mencukupi (Tersedia: {$freshItem->stock}).");
             }
        }

        $total = $this->cartService->total();
        $housingBlocks = HousingBlock::orderBy('name')->get();
        
        return view('checkout.form', compact(
            'cartItems',
            'total',
            'housingBlocks'
        ));
    }

    /**
     * Process checkout
     */
    public function store(CheckoutRequest $request)
    {
        try {
            // Validate stock before processing
            $cartItems = $this->cartService->get();
            $stockErrors = $this->stockService->validateCartAvailability($cartItems);

            if (!empty($stockErrors)) {
                return redirect()->route('cart.index')
                    ->withErrors($stockErrors);
            }

            $order = $this->checkoutService->createOrder(
                $request->customer_name,
                $request->housing_block_id,
                $request->delivery_type,
                $request->payment_method
            );

            // Redirect based on payment method
            if ($request->payment_method === 'qris') {
                return redirect()->route('payment.qris', ['code' => $order->code]);
            }

            // For cash payment, go directly to tracking
            return redirect()->route('tracking.show', ['code' => $order->code])
                ->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }
}
