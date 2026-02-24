<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

        $terminalStatuses = ['completed', 'cancelled', 'failed'];
        $isTerminal = in_array($order->status, $terminalStatuses);

        return view('tracking.show', [
            'order' => $order,
            'isTerminal' => $isTerminal,
        ]);
    }

    /**
     * Return current order status as JSON (for polling)
     */
    public function status(string $code): JsonResponse
    {
        $order = Order::where('code', $code)->first();

        if (!$order) {
            return response()->json(['error' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $order->status,
            'status_label' => $order->status_label,
        ]);
    }
}
