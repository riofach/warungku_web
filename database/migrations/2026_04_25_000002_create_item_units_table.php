<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('item_units')) {
            Schema::create('item_units', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('item_id')->constrained('items')->cascadeOnDelete();
                $table->string('label');
                $table->integer('quantity_base');
                $table->integer('sell_price')->default(0);
                $table->integer('buy_price')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('item_units');
    }
};
