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
        Schema::table('food_orders', function (Blueprint $table) {
            $table->decimal('tip_amount', 10, 2)->default(0)->after('total');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('tip_amount');
            $table->decimal('commission_amount', 10, 2)->default(0)->after('delivery_fee');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending')->after('commission_amount');
            $table->string('payment_reference')->nullable()->after('payment_status');
            $table->enum('delivery_method', ['delivery', 'pickup', 'offline_rider'])->default('delivery')->after('payment_reference');
            $table->json('shipping_address')->nullable()->after('delivery_method');
            $table->foreignId('rider_id')->nullable()->constrained('riders')->onDelete('set null')->after('vendor_id');
        });

        // Update status enum to include all needed statuses
        DB::statement("ALTER TABLE food_orders MODIFY status ENUM(
            'pending_payment',
            'awaiting_vendor',
            'accepted',
            'preparing',
            'ready_for_pickup',
            'assigned',
            'picked_up',
            'on_the_way',
            'delivered',
            'completed',
            'cancelled',
            'disputed'
        ) DEFAULT 'pending_payment'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            $table->dropColumn([
                'tip_amount',
                'delivery_fee',
                'commission_amount',
                'payment_status',
                'payment_reference',
                'delivery_method',
                'shipping_address',
                'rider_id'
            ]);
        });

        DB::statement("ALTER TABLE food_orders MODIFY status ENUM('pending', 'preparing', 'delivered', 'cancelled') DEFAULT 'pending'");
    }
};
