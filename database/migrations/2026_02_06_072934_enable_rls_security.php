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
            'settings' => ['Public Read Settings', 'Admin Write Settings'],
            'users' => ['Admin All Users']
        ];

        foreach ($policies as $table => $tablePolicies) {
            foreach ($tablePolicies as $policy) {
                DB::statement("DROP POLICY IF EXISTS \"{$policy}\" ON public.{$table}");
            }
        }

        // 3. Create Policies

        // --- Items & Categories & Housing Blocks (Public Read, Admin Write) ---
        DB::statement("CREATE POLICY \"Public Read Items\" ON public.items FOR SELECT USING (true)");
        DB::statement("CREATE POLICY \"Admin Write Items\" ON public.items FOR ALL USING (auth.role() = 'authenticated')");

        DB::statement("CREATE POLICY \"Public Read Categories\" ON public.categories FOR SELECT USING (true)");
        DB::statement("CREATE POLICY \"Admin Write Categories\" ON public.categories FOR ALL USING (auth.role() = 'authenticated')");

        DB::statement("CREATE POLICY \"Public Read Housing Blocks\" ON public.housing_blocks FOR SELECT USING (true)");
        DB::statement("CREATE POLICY \"Admin Write Housing Blocks\" ON public.housing_blocks FOR ALL USING (auth.role() = 'authenticated')");

        // --- Orders (Public Insert, Admin Read/Write) ---
        // Public (Website) can insert new orders.
        // We might need "Public Read Own Order" if we track via session/uuid, but usually strictly by code in Laravel controller is safer (Service Role).
        // However, Supabase Flutter needs to read them.
        DB::statement("CREATE POLICY \"Public Insert Orders\" ON public.orders FOR INSERT WITH CHECK (true)");
        DB::statement("CREATE POLICY \"Admin Read All Orders\" ON public.orders FOR ALL USING (auth.role() = 'authenticated')");
        
        // Order Items - same as orders
        DB::statement("CREATE POLICY \"Public Insert Order Items\" ON public.order_items FOR INSERT WITH CHECK (true)");
        DB::statement("CREATE POLICY \"Admin Read Order Items\" ON public.order_items FOR ALL USING (auth.role() = 'authenticated')");

        // --- Transactions (POS) - Admin Only ---
        DB::statement("CREATE POLICY \"Admin All Transactions\" ON public.transactions FOR ALL USING (auth.role() = 'authenticated')");
        DB::statement("CREATE POLICY \"Admin All Transaction Items\" ON public.transaction_items FOR ALL USING (auth.role() = 'authenticated')");

        // --- Settings (Public Read for some, Admin Write) ---
        DB::statement("CREATE POLICY \"Public Read Settings\" ON public.settings FOR SELECT USING (true)");
        DB::statement("CREATE POLICY \"Admin Write Settings\" ON public.settings FOR ALL USING (auth.role() = 'authenticated')");

        // --- Users (Admin Management) ---
        DB::statement("CREATE POLICY \"Admin All Users\" ON public.users FOR ALL USING (auth.role() = 'authenticated')");
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
