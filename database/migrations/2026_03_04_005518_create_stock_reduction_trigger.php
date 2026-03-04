<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates a PostgreSQL trigger that automatically reduces stock
     * when an order status changes to 'processing'.
     *
     * - Cash/Pickup orders: pending → processing (trigger runs, stock reduced)
     * - QRIS orders:        paid → processing   (trigger skips, already reduced)
     */
    public function up(): void
    {
        // 1. Create the trigger function
        DB::statement("
            CREATE OR REPLACE FUNCTION trigger_reduce_stock_on_order_processing()
            RETURNS TRIGGER AS \$\$
            BEGIN
                -- Only run when new status is 'processing'
                -- AND old status is NOT 'processing' (prevent double reduction)
                IF NEW.status = 'processing' AND OLD.status != 'processing' THEN
                    -- IMPORTANT: Skip if old status was 'paid' (QRIS orders)
                    -- Stock was already reduced when order entered 'paid' status
                    -- Only reduce for Cash orders (old status = 'pending')
                    IF OLD.status = 'pending' THEN
                        PERFORM reduce_stock_for_order(NEW.id);
                    END IF;
                END IF;

                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql SECURITY DEFINER;
        ");

        // 2. Attach trigger to orders table
        DB::statement("
            DROP TRIGGER IF EXISTS on_order_status_processing ON orders;
        ");

        DB::statement("
            CREATE TRIGGER on_order_status_processing
                AFTER UPDATE OF status ON orders
                FOR EACH ROW
                EXECUTE FUNCTION trigger_reduce_stock_on_order_processing();
        ");
    }

    /**
     * Reverse the migrations.
     * Removes the trigger and its function.
     */
    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS on_order_status_processing ON orders;");
        DB::statement("DROP FUNCTION IF EXISTS trigger_reduce_stock_on_order_processing();");
    }
};
