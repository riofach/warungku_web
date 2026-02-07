<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CartService;
use App\Models\Item;
use Illuminate\Support\Facades\Session;

class TestCart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-cart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually test CartService';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting CartService Test...');

        $cartService = new CartService();
        $itemId = '36c967b4-2399-42c7-996c-886ba0ade4d5';

        try {
            // Check item exists
            $item = Item::find($itemId);
            if (!$item) {
                $this->error("Item $itemId not found! Please create it first.");
                return;
            }
            $this->info("Found item: " . $item->name . " (Stock: " . $item->stock . ")");

            // Ensure cart is clear
            $cartService->clear();

            // Test Add
            $this->info('Adding 1 item...');
            $cartService->add($itemId, 1);
            $cart = Session::get('cart');
            
            if (isset($cart[$itemId]) && $cart[$itemId]['quantity'] == 1) {
                $this->info('✅ Add Success: Quantity 1');
            } else {
                $this->error('❌ Add Failed: ' . json_encode($cart));
            }

            // Test Add More
            $this->info('Adding 2 more items...');
            $cartService->add($itemId, 2);
            $cart = Session::get('cart');

            if (isset($cart[$itemId]) && $cart[$itemId]['quantity'] == 3) {
                $this->info('✅ Increment Success: Quantity 3');
            } else {
                $this->error('❌ Increment Failed: ' . json_encode($cart));
            }

            // Test Count
            $count = $cartService->count();
            if ($count == 3) {
                $this->info('✅ Count Success: 3');
            } else {
                $this->error("❌ Count Failed: Expected 3, got $count");
            }

            // Test Get
            $items = $cartService->get();
            if (count($items) > 0) {
                $this->info('✅ Get Success: Returns array');
            } else {
                $this->error('❌ Get Failed: Empty array');
            }

            // Test Clear
            $this->info('Clearing cart...');
            $cartService->clear();
            $items = $cartService->get();
            if (empty($items)) {
                $this->info('✅ Clear Success: Empty array');
            } else {
                $this->error('❌ Clear Failed: Not empty');
            }

        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
        }
    }
}
