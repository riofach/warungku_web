<?php

namespace App\Http\Controllers;

use App\Models\HousingBlock;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected CheckoutService $checkoutService;

    public function __construct(CartService $cartService, CheckoutService $checkoutService)
    {
        $this->cartService = $cartService;
        $this->checkoutService = $checkoutService;
    }

    /**
     * Display checkout form
     */
    public function index()
    {
        $cartItems = $this->cartService->getItems();
        
        if (empty($cartItems)) {
            return redirect()->route('shop.index')
                ->with('error', 'Keranjang belanja kosong');
        }

        $total = $this->cartService->getTotal();
        $housingBlocks = HousingBlock::orderBy('name')->get();
        $isDeliveryEnabled = Setting::isDeliveryEnabled();
        $isWarungOpen = Setting::isWarungOpen();

        return view('checkout.index', compact(
            'cartItems',
            'total',
            'housingBlocks',
            'isDeliveryEnabled',
            'isWarungOpen'
        ));
    }

    /**
     * Process checkout
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|min:2|max:255',
            'housing_block_id' => 'required|exists:housing_blocks,id',
            'delivery_type' => 'required|in:delivery,pickup',
            'payment_method' => 'required|in:cash,qris',
        ]);

        // Validate checkout
        $errors = $this->checkoutService->validateCheckout();
        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        try {
            $order = $this->checkoutService->createOrder(
                $request->customer_name,
                $request->housing_block_id,
                $request->delivery_type,
                $request->payment_method
            );

            // Redirect based on payment method
            if ($request->payment_method === Order::PAYMENT_QRIS) {
                return redirect()->route('payment.qris', $order->code);
            }

            // For cash payment, go directly to tracking
            return redirect()->route('tracking.show', $order->code)
                ->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }
}
