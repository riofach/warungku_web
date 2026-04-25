<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'has_units')) {
                $table->boolean('has_units')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('items', 'base_unit')) {
                $table->string('base_unit')->default('pcs')->after('has_units');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['has_units', 'base_unit']);
        });
    }
};
