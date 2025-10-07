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
        Schema::table('food_menus', function (Blueprint $table) {
            $table->index('vendor_id', 'idx_food_menus_vendor_id');
            $table->index('is_available', 'idx_food_menus_is_available');
            $table->index('category', 'idx_food_menus_category');
            $table->index(['vendor_id', 'is_available'], 'idx_food_menus_vendor_available');
        });

        Schema::table('food_orders', function (Blueprint $table) {
            $table->index('user_id', 'idx_food_orders_user_id');
            $table->index('vendor_id', 'idx_food_orders_vendor_id');
            $table->index('rider_id', 'idx_food_orders_rider_id');
            $table->index('status', 'idx_food_orders_status');
            $table->index('payment_status', 'idx_food_orders_payment_status');
            $table->index(['user_id', 'status'], 'idx_food_orders_user_status');
            $table->index(['vendor_id', 'status'], 'idx_food_orders_vendor_status');
            $table->index(['rider_id', 'status'], 'idx_food_orders_rider_status');
            $table->index(['payment_status', 'status'], 'idx_food_orders_payment_order_status');
            $table->index('created_at', 'idx_food_orders_created_at');
        });

        Schema::table('food_order_items', function (Blueprint $table) {
            $table->index('food_order_id', 'idx_food_order_items_order_id');
            $table->index('food_menu_id', 'idx_food_order_items_menu_id');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->index('wallet_id', 'idx_wallet_transactions_wallet_id');
            $table->index('order_id', 'idx_wallet_transactions_order_id');
            $table->index('order_type', 'idx_wallet_transactions_order_type');
            $table->index(['order_type', 'order_id'], 'idx_wallet_transactions_order_lookup');
            $table->index('status', 'idx_wallet_transactions_status');
            $table->index('created_at', 'idx_wallet_transactions_created_at');
        });

        Schema::table('food_vendors', function (Blueprint $table) {
            $table->index('vendor_id', 'idx_food_vendors_vendor_id');
            $table->index('is_open', 'idx_food_vendors_is_open');
            $table->index(['is_open', 'average_rating'], 'idx_food_vendors_open_rating');
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->index('vendor_id', 'idx_riders_vendor_id');
            $table->index('status', 'idx_riders_status');
            $table->index('is_available', 'idx_riders_is_available');
            $table->index(['status', 'is_available'], 'idx_riders_status_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_menus', function (Blueprint $table) {
            $table->dropIndex('idx_food_menus_vendor_id');
            $table->dropIndex('idx_food_menus_is_available');
            $table->dropIndex('idx_food_menus_category');
            $table->dropIndex('idx_food_menus_vendor_available');
        });

        Schema::table('food_orders', function (Blueprint $table) {
            $table->dropIndex('idx_food_orders_user_id');
            $table->dropIndex('idx_food_orders_vendor_id');
            $table->dropIndex('idx_food_orders_rider_id');
            $table->dropIndex('idx_food_orders_status');
            $table->dropIndex('idx_food_orders_payment_status');
            $table->dropIndex('idx_food_orders_user_status');
            $table->dropIndex('idx_food_orders_vendor_status');
            $table->dropIndex('idx_food_orders_rider_status');
            $table->dropIndex('idx_food_orders_payment_order_status');
            $table->dropIndex('idx_food_orders_created_at');
        });

        Schema::table('food_order_items', function (Blueprint $table) {
            $table->dropIndex('idx_food_order_items_order_id');
            $table->dropIndex('idx_food_order_items_menu_id');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_wallet_transactions_wallet_id');
            $table->dropIndex('idx_wallet_transactions_order_id');
            $table->dropIndex('idx_wallet_transactions_order_type');
            $table->dropIndex('idx_wallet_transactions_order_lookup');
            $table->dropIndex('idx_wallet_transactions_status');
            $table->dropIndex('idx_wallet_transactions_created_at');
        });

        Schema::table('food_vendors', function (Blueprint $table) {
            $table->dropIndex('idx_food_vendors_vendor_id');
            $table->dropIndex('idx_food_vendors_is_open');
            $table->dropIndex('idx_food_vendors_open_rating');
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropIndex('idx_riders_vendor_id');
            $table->dropIndex('idx_riders_status');
            $table->dropIndex('idx_riders_is_available');
            $table->dropIndex('idx_riders_status_available');
        });
    }
};
