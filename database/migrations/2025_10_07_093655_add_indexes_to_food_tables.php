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
        Schema::table('food_menus', function (Blueprint $table) {
            if (!$this->indexExists('food_menus', 'idx_food_menus_vendor_id')) {
                $table->index('vendor_id', 'idx_food_menus_vendor_id');
            }
            if (Schema::hasColumn('food_menus', 'is_available') && !$this->indexExists('food_menus', 'idx_food_menus_is_available')) {
                $table->index('is_available', 'idx_food_menus_is_available');
            }
            if (Schema::hasColumn('food_menus', 'category') && !$this->indexExists('food_menus', 'idx_food_menus_category')) {
                $table->index('category', 'idx_food_menus_category');
            }
            if (Schema::hasColumn('food_menus', 'is_available') && !$this->indexExists('food_menus', 'idx_food_menus_vendor_available')) {
                $table->index(['vendor_id', 'is_available'], 'idx_food_menus_vendor_available');
            }
        });

        Schema::table('food_orders', function (Blueprint $table) {
            if (!$this->indexExists('food_orders', 'idx_food_orders_user_id')) {
                $table->index('user_id', 'idx_food_orders_user_id');
            }
            if (!$this->indexExists('food_orders', 'idx_food_orders_vendor_id')) {
                $table->index('vendor_id', 'idx_food_orders_vendor_id');
            }
            if (Schema::hasColumn('food_orders', 'rider_id') && !$this->indexExists('food_orders', 'idx_food_orders_rider_id')) {
                $table->index('rider_id', 'idx_food_orders_rider_id');
            }
            if (!$this->indexExists('food_orders', 'idx_food_orders_status')) {
                $table->index('status', 'idx_food_orders_status');
            }
            if (Schema::hasColumn('food_orders', 'payment_status') && !$this->indexExists('food_orders', 'idx_food_orders_payment_status')) {
                $table->index('payment_status', 'idx_food_orders_payment_status');
            }
            if (!$this->indexExists('food_orders', 'idx_food_orders_user_status')) {
                $table->index(['user_id', 'status'], 'idx_food_orders_user_status');
            }
            if (!$this->indexExists('food_orders', 'idx_food_orders_vendor_status')) {
                $table->index(['vendor_id', 'status'], 'idx_food_orders_vendor_status');
            }
            if (Schema::hasColumn('food_orders', 'rider_id') && !$this->indexExists('food_orders', 'idx_food_orders_rider_status')) {
                $table->index(['rider_id', 'status'], 'idx_food_orders_rider_status');
            }
            if (Schema::hasColumn('food_orders', 'payment_status') && !$this->indexExists('food_orders', 'idx_food_orders_payment_order_status')) {
                $table->index(['payment_status', 'status'], 'idx_food_orders_payment_order_status');
            }
            if (!$this->indexExists('food_orders', 'idx_food_orders_created_at')) {
                $table->index('created_at', 'idx_food_orders_created_at');
            }
        });

        Schema::table('food_order_items', function (Blueprint $table) {
            if (!$this->indexExists('food_order_items', 'idx_food_order_items_order_id')) {
                $table->index('food_order_id', 'idx_food_order_items_order_id');
            }
            if (!$this->indexExists('food_order_items', 'idx_food_order_items_menu_id')) {
                $table->index('food_menu_id', 'idx_food_order_items_menu_id');
            }
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!$this->indexExists('wallet_transactions', 'idx_wallet_transactions_wallet_id')) {
                $table->index('wallet_id', 'idx_wallet_transactions_wallet_id');
            }
            if (Schema::hasColumn('wallet_transactions', 'order_id') && !$this->indexExists('wallet_transactions', 'idx_wallet_transactions_order_id')) {
                $table->index('order_id', 'idx_wallet_transactions_order_id');
            }
            if (Schema::hasColumn('wallet_transactions', 'order_type') && !$this->indexExists('wallet_transactions', 'idx_wallet_transactions_order_type')) {
                $table->index('order_type', 'idx_wallet_transactions_order_type');
            }
            if (Schema::hasColumn('wallet_transactions', 'order_type') && Schema::hasColumn('wallet_transactions', 'order_id') && !$this->indexExists('wallet_transactions', 'idx_wallet_transactions_order_lookup')) {
                $table->index(['order_type', 'order_id'], 'idx_wallet_transactions_order_lookup');
            }
            if (!$this->indexExists('wallet_transactions', 'idx_wallet_transactions_status')) {
                $table->index('status', 'idx_wallet_transactions_status');
            }
            if (!$this->indexExists('wallet_transactions', 'idx_wallet_transactions_created_at')) {
                $table->index('created_at', 'idx_wallet_transactions_created_at');
            }
        });

        Schema::table('food_vendors', function (Blueprint $table) {
            if (!$this->indexExists('food_vendors', 'idx_food_vendors_vendor_id')) {
                $table->index('vendor_id', 'idx_food_vendors_vendor_id');
            }
            if (Schema::hasColumn('food_vendors', 'is_open') && !$this->indexExists('food_vendors', 'idx_food_vendors_is_open')) {
                $table->index('is_open', 'idx_food_vendors_is_open');
            }
            if (Schema::hasColumn('food_vendors', 'is_open') && Schema::hasColumn('food_vendors', 'average_rating') && !$this->indexExists('food_vendors', 'idx_food_vendors_open_rating')) {
                $table->index(['is_open', 'average_rating'], 'idx_food_vendors_open_rating');
            }
        });

        Schema::table('riders', function (Blueprint $table) {
            if (!$this->indexExists('riders', 'idx_riders_vendor_id')) {
                $table->index('vendor_id', 'idx_riders_vendor_id');
            }
            if (!$this->indexExists('riders', 'idx_riders_status')) {
                $table->index('status', 'idx_riders_status');
            }
            if (Schema::hasColumn('riders', 'is_available') && !$this->indexExists('riders', 'idx_riders_is_available')) {
                $table->index('is_available', 'idx_riders_is_available');
            }
            if (Schema::hasColumn('riders', 'is_available') && !$this->indexExists('riders', 'idx_riders_status_available')) {
                $table->index(['status', 'is_available'], 'idx_riders_status_available');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_menus', function (Blueprint $table) {
            $indexes = ['idx_food_menus_vendor_id', 'idx_food_menus_is_available', 'idx_food_menus_category', 'idx_food_menus_vendor_available'];
            foreach ($indexes as $index) {
                if ($this->indexExists('food_menus', $index)) {
                    $table->dropIndex($index);
                }
            }
        });

        Schema::table('food_orders', function (Blueprint $table) {
            $indexes = [
                'idx_food_orders_user_id', 'idx_food_orders_vendor_id', 'idx_food_orders_rider_id',
                'idx_food_orders_status', 'idx_food_orders_payment_status', 'idx_food_orders_user_status',
                'idx_food_orders_vendor_status', 'idx_food_orders_rider_status',
                'idx_food_orders_payment_order_status', 'idx_food_orders_created_at'
            ];
            foreach ($indexes as $index) {
                if ($this->indexExists('food_orders', $index)) {
                    $table->dropIndex($index);
                }
            }
        });

        Schema::table('food_order_items', function (Blueprint $table) {
            $indexes = ['idx_food_order_items_order_id', 'idx_food_order_items_menu_id'];
            foreach ($indexes as $index) {
                if ($this->indexExists('food_order_items', $index)) {
                    $table->dropIndex($index);
                }
            }
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $indexes = [
                'idx_wallet_transactions_wallet_id', 'idx_wallet_transactions_order_id',
                'idx_wallet_transactions_order_type', 'idx_wallet_transactions_order_lookup',
                'idx_wallet_transactions_status', 'idx_wallet_transactions_created_at'
            ];
            foreach ($indexes as $index) {
                if ($this->indexExists('wallet_transactions', $index)) {
                    $table->dropIndex($index);
                }
            }
        });

        Schema::table('food_vendors', function (Blueprint $table) {
            $indexes = ['idx_food_vendors_vendor_id', 'idx_food_vendors_is_open', 'idx_food_vendors_open_rating'];
            foreach ($indexes as $index) {
                if ($this->indexExists('food_vendors', $index)) {
                    $table->dropIndex($index);
                }
            }
        });

        Schema::table('riders', function (Blueprint $table) {
            $indexes = ['idx_riders_vendor_id', 'idx_riders_status', 'idx_riders_is_available', 'idx_riders_status_available'];
            foreach ($indexes as $index) {
                if ($this->indexExists('riders', $index)) {
                    $table->dropIndex($index);
                }
            }
        });
    }

    /**
     * Check if an index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();

        $result = DB::select(
            "SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$databaseName, $table, $index]
        );

        return $result[0]->count > 0;
    }
};
