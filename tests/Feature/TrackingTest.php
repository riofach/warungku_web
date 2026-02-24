<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HousingBlock;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helper: Create a full order with items
    // ---------------------------------------------------------------
    private function createOrder(array $overrides = []): Order
    {
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'buy_price' => 10000,
            'sell_price' => 15000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $order = Order::create(array_merge([
            'code' => 'WRG-20260115-0001',
            'customer_name' => 'Budi',
            'payment_method' => 'qris',
            'delivery_type' => 'pickup',
            'status' => 'pending',
            'total' => 15000,
        ], $overrides));

        OrderItem::create([
            'order_id' => $order->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'price' => 15000,
            'subtotal' => 15000,
        ]);

        return $order;
    }

    // ==============================================================
    // Scenario 1: Initial Tracking Screen (AC1)
    // ==============================================================

    public function test_tracking_index_page_displays_search_form(): void
    {
        $response = $this->get(route('tracking.index'));

        $response->assertStatus(200);
        $response->assertSee('Lacak Pesanan');
        $response->assertSee('Kode Pesanan');
        $response->assertSee('WRG-');
    }

    // ==============================================================
    // Scenario 2: Successful Order Lookup (AC2)
    // ==============================================================

    public function test_search_with_valid_code_redirects_to_show_page(): void
    {
        $this->withoutMiddleware();
        $order = $this->createOrder();

        $response = $this->from(route('tracking.index'))
            ->post(route('tracking.search'), [
                'code' => $order->code,
            ]);

        $response->assertRedirect(route('tracking.show', $order->code));
    }

    public function test_show_page_displays_order_status_badge(): void
    {
        $order = $this->createOrder(['status' => 'paid']);

        $response = $this->get(route('tracking.show', $order->code));

        $response->assertStatus(200);
        $response->assertSee($order->code);
        $response->assertSee('Dibayar');
    }

    public function test_show_page_displays_order_details(): void
    {
        $block = HousingBlock::create(['name' => 'Blok A']);
        $order = $this->createOrder([
            'delivery_type' => 'delivery',
            'housing_block_id' => $block->id,
            'customer_name' => 'Siti Rahayu',
            'status' => 'processing',
        ]);

        $response = $this->get(route('tracking.show', $order->code));

        $response->assertStatus(200);
        $response->assertSee('Siti Rahayu');
        $response->assertSee('Blok A');
        $response->assertSee('Nasi Goreng');
        $response->assertSee('15.000');
    }

    public function test_show_page_displays_timeline_component(): void
    {
        $order = $this->createOrder(['status' => 'processing']);

        $response = $this->get(route('tracking.show', $order->code));

        $response->assertStatus(200);
        $response->assertSee('Pesanan Dibuat');
        $response->assertSee('Dibayar');
        $response->assertSee('Dikemas');
        $response->assertSee('Selesai');
    }

    public function test_show_page_displays_action_buttons(): void
    {
        $order = $this->createOrder(['status' => 'pending']);

        $response = $this->get(route('tracking.show', $order->code));

        $response->assertStatus(200);
        $response->assertSee('Download Invoice');
        $response->assertSee('Chat Admin');
    }

    public function test_download_invoice_button_is_disabled_when_pending(): void
    {
        $order = $this->createOrder(['status' => 'pending']);

        $response = $this->get(route('tracking.show', $order->code));

        $response->assertSee('disabled', false);
    }

    public function test_search_is_case_insensitive_for_order_code(): void
    {
        $this->withoutMiddleware();
        $order = $this->createOrder(['code' => 'WRG-20260115-0001']);

        $response = $this->from(route('tracking.index'))
            ->post(route('tracking.search'), [
                'code' => 'wrg-20260115-0001',
            ]);

        $response->assertRedirect(route('tracking.show', 'WRG-20260115-0001'));
    }

    // ==============================================================
    // Scenario 3: Order Not Found (AC3)
    // ==============================================================

    public function test_search_with_invalid_code_shows_error_message(): void
    {
        $this->withoutMiddleware();
        // Create an order first to ensure DB connection is warm
        $this->createOrder(['code' => 'WRG-20260115-0099']);

        $response = $this->from(route('tracking.index'))
            ->post(route('tracking.search'), [
                'code' => 'WRG-99999999-9999',
            ]);

        $response->assertRedirect(route('tracking.index'));
        $response->assertSessionHas('error');

        $this->assertStringContainsString('WRG-99999999-9999', session('error'));
        $this->assertStringContainsString('tidak ditemukan', session('error'));
    }

    public function test_search_with_invalid_code_preserves_input_on_form(): void
    {
        $this->withoutMiddleware();
        // Create an order first to ensure DB connection is warm
        $this->createOrder(['code' => 'WRG-20260115-0098']);

        $response = $this->from(route('tracking.index'))
            ->post(route('tracking.search'), [
                'code' => 'WRG-INVALID-0000',
            ]);

        $response->assertRedirect(route('tracking.index'));
        $response->assertSessionHas('searched_code', 'WRG-INVALID-0000');
    }

    public function test_search_requires_code_field(): void
    {
        $this->withoutMiddleware();
        // Create an order first to ensure DB connection is warm
        $this->createOrder(['code' => 'WRG-20260115-0097']);

        $response = $this->from(route('tracking.index'))
            ->post(route('tracking.search'), [
                'code' => '',
            ]);

        $response->assertRedirect(route('tracking.index'));
        $response->assertSessionHasErrors(['code']);
    }

    public function test_show_page_returns_404_for_unknown_code(): void
    {
        $response = $this->get('/tracking/WRG-99999999-9999');

        $response->assertStatus(404);
    }

    // ==============================================================
    // Status-specific timeline tests
    // ==============================================================

    public function test_show_page_handles_cancelled_status(): void
    {
        $order = $this->createOrder(['status' => 'cancelled']);

        $response = $this->get(route('tracking.show', $order->code));

        $response->assertStatus(200);
        $response->assertSee('Dibatalkan');
    }

    public function test_show_page_handles_completed_status(): void
    {
        $order = $this->createOrder(['status' => 'completed']);

        $response = $this->get(route('tracking.show', $order->code));

        $response->assertStatus(200);
        $response->assertSee('Selesai');
    }

    // ==============================================================
    // Story 12.2: Live Status Updates via Polling (AC1, AC5)
    // ==============================================================

    public function test_status_endpoint_returns_json(): void
    {
        $order = $this->createOrder(['status' => 'processing']);

        $response = $this->getJson(route('tracking.status', $order->code));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'processing',
            'status_label' => $order->status_label,
        ]);
    }

    public function test_status_endpoint_returns_404_for_unknown_code(): void
    {
        $response = $this->getJson(route('tracking.status', 'NONEXISTENT'));

        $response->assertStatus(404);
        $response->assertJson(['error' => 'not_found']);
    }

    public function test_show_page_includes_polling_script_for_active_order(): void
    {
        $order = $this->createOrder(['status' => 'processing']);

        $response = $this->get(route('tracking.show', $order->code));

        $response->assertStatus(200);
        $response->assertSee('live-indicator', false);
        $response->assertSee('pollStatus', false);
        $response->assertSee('POLL_INTERVAL', false);
    }

    public function test_show_page_excludes_polling_script_for_terminal_status(): void
    {
        foreach (['completed', 'cancelled', 'failed'] as $status) {
            $order = $this->createOrder(['status' => $status, 'code' => 'WRG-20260115-00' . rand(10, 99)]);

            $response = $this->get(route('tracking.show', $order->code));

            $response->assertStatus(200);
            $response->assertViewHas('isTerminal', true);
            $response->assertDontSee('pollStatus', false);
        }
    }
}
