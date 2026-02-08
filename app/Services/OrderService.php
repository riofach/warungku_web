<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;

class OrderService
{
    /**
     * Generate a unique order code.
     * Format: WRG-YYYYMMDD-XXXX
     * 
     * NOTE: This method should be called within a database transaction 
     * to ensure the lock is held until the new order is inserted.
     */
    public function generateUniqueCode(): string
    {
        // Lock the latest order to ensure sequential generation
        // This prevents race conditions when multiple orders are created simultaneously
        $query = Order::latest('id');

        // Skip locking in tests to prevent SQLite/Driver deadlocks
        if (!app()->runningUnitTests()) {
            $query->lockForUpdate();
        }

        $lastOrder = $query->first();

        $today = Carbon::now();
        $dateStr = $today->format('Ymd');
        $prefix = "WRG-{$dateStr}-";

        if (!$lastOrder) {
            return $prefix . '0001';
        }

        // Parse the last order code to determine the next sequence
        // Format: WRG-YYYYMMDD-XXXX
        $parts = explode('-', $lastOrder->code);

        // Check if the last order matches today's date format
        if (count($parts) === 3 && $parts[1] === $dateStr) {
            $sequence = (int)$parts[2];
            $nextSequence = $sequence + 1;
            return $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        }

        // If last order was not today, reset sequence
        return $prefix . '0001';
    }
}
