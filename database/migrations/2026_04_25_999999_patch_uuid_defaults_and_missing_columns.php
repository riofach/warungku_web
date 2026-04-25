<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Safety-net migration that runs last (timestamp 999999).
 * Adds gen_random_uuid() defaults to all UUID PKs and restores columns
 * that are managed by Supabase migrations but absent from Laravel Blueprint.
 *
 * Running migrate:fresh will recreate all tables, then this migration
 * ensures the schema is compatible with the Flutter app and Supabase RPCs.
 */
return new class extends Migration
{
    public function up(): void
    {
        $uuidTables = [
            'categories', 'items', 'orders', 'order_items',
            'transactions', 'transaction_items', 'item_units',
        ];

        foreach ($uuidTables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN id SET DEFAULT gen_random_uuid()");
            }
        }

        // items.category_id must be nullable (Flutter allows items without category)
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'category_id')) {
            DB::statement("ALTER TABLE items ALTER COLUMN category_id DROP NOT NULL");
        }

        // transaction_items: restore Supabase-managed columns
        if (Schema::hasTable('transaction_items')) {
            DB::statement("ALTER TABLE transaction_items ADD COLUMN IF NOT EXISTS buy_price integer NOT NULL DEFAULT 0");
            DB::statement("ALTER TABLE transaction_items ADD COLUMN IF NOT EXISTS item_unit_id uuid REFERENCES item_units(id) ON DELETE SET NULL");
            DB::statement("ALTER TABLE transaction_items ADD COLUMN IF NOT EXISTS quantity_base_used integer NOT NULL DEFAULT 1");
        }

        // purchases table: not in any base Laravel migration
        if (!Schema::hasTable('purchases')) {
            DB::statement("
                CREATE TABLE purchases (
                    id             uuid        NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
                    item_id        uuid        NOT NULL REFERENCES items(id) ON DELETE CASCADE,
                    admin_id       uuid,
                    quantity_base  integer     NOT NULL,
                    total_cost     integer     NOT NULL,
                    cost_per_base  numeric(12,4) NOT NULL DEFAULT 0,
                    notes          text,
                    created_at     timestamp   NOT NULL DEFAULT now(),
                    updated_at     timestamp   NOT NULL DEFAULT now()
                )
            ");
        }

        // Enable RLS on purchases
        DB::statement("ALTER TABLE purchases ENABLE ROW LEVEL SECURITY");
        DB::statement("DROP POLICY IF EXISTS \"Admin All Purchases\" ON purchases");
        DB::statement("CREATE POLICY \"Admin All Purchases\" ON purchases FOR ALL USING (auth.role() = 'authenticated')");

        // Enable RLS on transaction_items (policy may have been dropped)
        DB::statement("ALTER TABLE transaction_items ENABLE ROW LEVEL SECURITY");
        DB::statement("DROP POLICY IF EXISTS \"Admin All Transaction Items\" ON transaction_items");
        DB::statement("CREATE POLICY \"Admin All Transaction Items\" ON transaction_items FOR ALL USING (auth.role() = 'authenticated')");
    }

    public function down(): void
    {
        // Intentionally a no-op: removing uuid defaults would break the app
    }
};
