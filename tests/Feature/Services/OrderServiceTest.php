<?php

namespace Tests\Feature\Services;

use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }

    public function test_it_generates_code_with_correct_format()
    {
        Carbon::setTestNow('2026-01-28 10:00:00');

        $code = $this->orderService->generateUniqueCode();

        $this->assertEquals('WRG-20260128-0001', $code);
    }

    public function test_it_increments_sequence_for_same_day()
    {
        Carbon::setTestNow('2026-01-28 10:00:00');

        // Create existing order
        Order::create([
            'code' => 'WRG-20260128-0001',
            'customer_name' => 'Test',
            'payment_method' => 'cash',
            'delivery_type' => 'pickup',
            'total' => 10000
        ]);

        $code = $this->orderService->generateUniqueCode();

        $this->assertEquals('WRG-20260128-0002', $code);
    }

    public function test_it_resets_sequence_for_new_day()
    {
        // Yesterday's order
        Carbon::setTestNow('2026-01-27 10:00:00');
        Order::create([
            'code' => 'WRG-20260127-0005',
            'customer_name' => 'Test',
            'payment_method' => 'cash',
            'delivery_type' => 'pickup',
            'total' => 10000
        ]);

        // Today
        Carbon::setTestNow('2026-01-28 10:00:00');

        $code = $this->orderService->generateUniqueCode();

        $this->assertEquals('WRG-20260128-0001', $code);
    }
}
