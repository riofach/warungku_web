<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('block_address')->nullable()->after('code');
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'housing_block_id')) {
                $table->dropConstrainedForeignId('housing_block_id');
            }
        });

        Schema::dropIfExists('housing_blocks');
    }

    public function down(): void
    {
        Schema::create('housing_blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUuid('housing_block_id')->nullable()->constrained('housing_blocks')->nullOnDelete();
            $table->dropColumn('block_address');
        });
    }
};
