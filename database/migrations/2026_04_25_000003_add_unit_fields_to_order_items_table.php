<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'item_unit_id')) {
                $table->uuid('item_unit_id')->nullable()->after('item_id');
                $table->foreign('item_unit_id')->references('id')->on('item_units')->nullOnDelete();
            }
            if (!Schema::hasColumn('order_items', 'buy_price')) {
                $table->integer('buy_price')->default(0)->after('price');
            }
            if (!Schema::hasColumn('order_items', 'quantity_base_used')) {
                $table->integer('quantity_base_used')->default(1)->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['item_unit_id']);
            $table->dropColumn(['item_unit_id', 'buy_price', 'quantity_base_used']);
        });
    }
};
