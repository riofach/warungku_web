<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Display tracking search form
     */
    public function index()
    {
        return view('tracking.index');
    }

    /**
     * Search order by code
     */
    public function search(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = strtoupper(trim($request->code));
        $order = Order::where('code', $code)->first();

        if (!$order) {
            return back()
                ->with('error', "Pesanan dengan kode {$code} tidak ditemukan")
                ->with('searched_code', $code)
                ->withInput();
        }

        return redirect()->route('tracking.show', $order->code);
    }

    /**
     * Display order tracking
     */
    public function show(string $code)
    {
        $order = Order::where('code', $code)
            ->with(['orderItems.item', 'housingBlock'])
            ->firstOrFail();

        return view('tracking.show', compact('order'));
    }
}
