<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. housing_blocks
        Schema::create('housing_blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        // 2. orders
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // WRG-YYYYMMDD-XXXX
            // Allow nullable for guest/general orders initially or if block deleted, but usually required
            $table->foreignUuid('housing_block_id')->nullable()->constrained('housing_blocks')->nullOnDelete();
            $table->string('customer_name');
            $table->string('payment_method'); // 'qris' or 'cash'
            $table->string('delivery_type'); // 'pickup' or 'delivery'
            $table->string('status')->default('pending'); // pending, paid, processing, ready, completed, failed, cancelled
            $table->integer('total');
            $table->timestamps();
        });

        // 3. order_items
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('items'); // Don't cascade delete items? Maybe null on delete or restrict.
            
            $table->integer('quantity');
            $table->integer('price'); // Price at time of order
            $table->integer('subtotal');
            $table->timestamps();
        });

        // 4. transactions (POS)
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // TRX-YYYYMMDD-XXXX
            // admin_id needed? FR5: "Sistem membedakan transaksi berdasarkan admin yang login". 
            // Architecture: "public.users - Admin accounts (Supabase Auth)".
            // Since Admin is Supabase Auth User, we store UUID string.
            $table->uuid('admin_id')->nullable(); // Store Supabase Auth UID
            
            $table->string('payment_method'); // 'cash' or 'qris'
            $table->integer('cash_received')->nullable();
            $table->integer('change')->nullable();
            $table->integer('total');
            $table->timestamps();
        });

        // 5. transaction_items
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('items');
            $table->integer('quantity');
            $table->integer('price');
            $table->integer('subtotal');
            $table->timestamps();
        });

        // 6. settings
        Schema::create('settings', function (Blueprint $table) {
            // Key-Value store
            $table->string('key')->primary(); // e.g. 'operating_hours', 'whatsapp_number'
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('housing_blocks');
    }
};
