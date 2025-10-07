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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('ref');
            $table->string('order_type')->nullable()->after('order_id'); // 'food_order', 'ecommerce_order', 'service_order', etc.
            $table->string('performed_by')->nullable()->after('wallet_id'); // 'user', 'system', 'admin'
            $table->text('description')->nullable()->after('performed_by');
            $table->string('related_type')->nullable()->after('description');
            $table->unsignedBigInteger('related_id')->nullable()->after('related_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'order_type', 'performed_by', 'description', 'related_type', 'related_id']);
        });
    }
};
