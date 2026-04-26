<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('item_units')) {
            return;
        }

        DB::statement('ALTER TABLE public.item_units ENABLE ROW LEVEL SECURITY');
        DB::statement('DROP POLICY IF EXISTS "Public Read Item Units" ON public.item_units');
        DB::statement('DROP POLICY IF EXISTS "Admin Write Item Units" ON public.item_units');
        DB::statement('CREATE POLICY "Public Read Item Units" ON public.item_units FOR SELECT USING (true)');
        DB::statement('CREATE POLICY "Admin Write Item Units" ON public.item_units FOR ALL USING (auth.role() = \'authenticated\')');
    }

    public function down(): void
    {
        // Intentionally a no-op to avoid weakening security on rollback.
    }
};
