<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrdersMigrationTest extends TestCase
{
    /**
     * Test that the orders table has the required payment fields.
     */
    public function test_orders_table_has_payment_fields(): void
    {
        $this->assertTrue(Schema::hasColumn('orders', 'payment_url'));
        $this->assertTrue(Schema::hasColumn('orders', 'payment_token'));
        $this->assertTrue(Schema::hasColumn('orders', 'payment_expires_at'));
    }
}
