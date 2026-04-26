<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $applyStatementsIfTableExists = function (string $table, array $statements): void {
            if (!Schema::hasTable($table)) {
                return;
            }

            foreach ($statements as $statement) {
                DB::statement($statement);
            }
        };

        // 1. Enable RLS on all tables
        $tables = [
            'users',
            'categories',
            'items',
            'housing_blocks',
            'orders',
            'order_items',
            'transactions',
            'transaction_items',
            'item_units',
            'purchases',
            'settings',
            // Laravel system tables
            'migrations',
            'failed_jobs',
            'jobs',
            'job_batches',
            'cache',
            'cache_locks',
            'sessions',
            'password_reset_tokens'
        ];

        foreach ($tables as $table) {
            // Check if table exists before altering (just in case)
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE public.{$table} ENABLE ROW LEVEL SECURITY");
            }
        }

        // 2. Drop existing policies to ensure clean slate
        $policies = [
            'items' => ['Public Read Items', 'Admin Write Items'],
            'categories' => ['Public Read Categories', 'Admin Write Categories'],
            'housing_blocks' => ['Public Read Housing Blocks', 'Admin Write Housing Blocks'],
            'orders' => ['Public Insert Orders', 'Admin Read All Orders', 'Public Read Own Order'],
            'order_items' => ['Public Insert Order Items', 'Admin Read Order Items'],
            'transactions' => ['Admin All Transactions'],
            'transaction_items' => ['Admin All Transaction Items'],
            'item_units' => ['Public Read Item Units', 'Admin Write Item Units'],
            'purchases' => ['Admin All Purchases'],
            'settings' => ['Public Read Settings', 'Admin Write Settings'],
            'users' => ['Admin All Users']
        ];

        foreach ($policies as $table => $tablePolicies) {
            $applyStatementsIfTableExists(
                $table,
                array_map(
                    fn (string $policy) => "DROP POLICY IF EXISTS \"{$policy}\" ON public.{$table}",
                    $tablePolicies
                )
            );
        }

        // 3. Create Policies

        // --- Items & Categories & Housing Blocks (Public Read, Admin Write) ---
        $applyStatementsIfTableExists('items', [
            "CREATE POLICY \"Public Read Items\" ON public.items FOR SELECT USING (true)",
            "CREATE POLICY \"Admin Write Items\" ON public.items FOR ALL USING (auth.role() = 'authenticated')",
        ]);

        $applyStatementsIfTableExists('categories', [
            "CREATE POLICY \"Public Read Categories\" ON public.categories FOR SELECT USING (true)",
            "CREATE POLICY \"Admin Write Categories\" ON public.categories FOR ALL USING (auth.role() = 'authenticated')",
        ]);

        $applyStatementsIfTableExists('housing_blocks', [
            "CREATE POLICY \"Public Read Housing Blocks\" ON public.housing_blocks FOR SELECT USING (true)",
            "CREATE POLICY \"Admin Write Housing Blocks\" ON public.housing_blocks FOR ALL USING (auth.role() = 'authenticated')",
        ]);

        // --- Orders (Public Insert, Admin Read/Write) ---
        // Public (Website) can insert new orders.
        // We might need "Public Read Own Order" if we track via session/uuid, but usually strictly by code in Laravel controller is safer (Service Role).
        // However, Supabase Flutter needs to read them.
        $applyStatementsIfTableExists('orders', [
            "CREATE POLICY \"Public Insert Orders\" ON public.orders FOR INSERT WITH CHECK (true)",
            "CREATE POLICY \"Admin Read All Orders\" ON public.orders FOR ALL USING (auth.role() = 'authenticated')",
        ]);
        
        // Order Items - same as orders
        $applyStatementsIfTableExists('order_items', [
            "CREATE POLICY \"Public Insert Order Items\" ON public.order_items FOR INSERT WITH CHECK (true)",
            "CREATE POLICY \"Admin Read Order Items\" ON public.order_items FOR ALL USING (auth.role() = 'authenticated')",
        ]);

        // --- Transactions (POS) - Admin Only ---
        $applyStatementsIfTableExists('transactions', [
            "CREATE POLICY \"Admin All Transactions\" ON public.transactions FOR ALL USING (auth.role() = 'authenticated')",
        ]);
        $applyStatementsIfTableExists('transaction_items', [
            "CREATE POLICY \"Admin All Transaction Items\" ON public.transaction_items FOR ALL USING (auth.role() = 'authenticated')",
        ]);

        // --- Settings (Public Read for some, Admin Write) ---
        $applyStatementsIfTableExists('settings', [
            "CREATE POLICY \"Public Read Settings\" ON public.settings FOR SELECT USING (true)",
            "CREATE POLICY \"Admin Write Settings\" ON public.settings FOR ALL USING (auth.role() = 'authenticated')",
        ]);

        // --- Item Units (Public Read for POS, Admin Write) ---
        $applyStatementsIfTableExists('item_units', [
            "CREATE POLICY \"Public Read Item Units\" ON public.item_units FOR SELECT USING (true)",
            "CREATE POLICY \"Admin Write Item Units\" ON public.item_units FOR ALL USING (auth.role() = 'authenticated')",
        ]);

        // --- Purchases (Admin Only) ---
        $applyStatementsIfTableExists('purchases', [
            "CREATE POLICY \"Admin All Purchases\" ON public.purchases FOR ALL USING (auth.role() = 'authenticated')",
        ]);

        // --- Users (Admin Management) ---
        $applyStatementsIfTableExists('users', [
            "CREATE POLICY \"Admin All Users\" ON public.users FOR ALL USING (auth.role() = 'authenticated')",
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable RLS (Not recommended to automate fully in down for security, but for consistency)
        $tables = [
            'users',
            'categories',
            'items',
            'housing_blocks',
            'orders',
            'order_items',
            'transactions',
            'transaction_items',
            'item_units',
            'purchases',
            'settings',
            'migrations',
            'failed_jobs',
            'jobs',
            'job_batches',
            'cache',
            'cache_locks',
            'sessions',
            'password_reset_tokens'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE public.{$table} DISABLE ROW LEVEL SECURITY");
            }
        }
    }
};
