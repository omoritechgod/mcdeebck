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
        Schema::table('food_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('food_order_items', 'total_price')) {
                $table->decimal('total_price', 10, 2)->default(0)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('food_order_items', 'total_price')) {
                $table->dropColumn('total_price');
            }
        });
    }
};
