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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_url')->nullable()->after('status');
            $table->string('payment_token')->nullable()->after('payment_url');
            $table->timestamp('payment_expires_at')->nullable()->after('payment_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_url', 'payment_token', 'payment_expires_at']);
        });
    }
};
