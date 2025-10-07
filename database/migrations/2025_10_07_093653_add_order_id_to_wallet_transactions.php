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
            if (!Schema::hasColumn('wallet_transactions', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('ref');
            }
            if (!Schema::hasColumn('wallet_transactions', 'order_type')) {
                $table->string('order_type')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('wallet_transactions', 'performed_by')) {
                $table->string('performed_by')->nullable()->after('wallet_id');
            }
            if (!Schema::hasColumn('wallet_transactions', 'description')) {
                $table->text('description')->nullable()->after('performed_by');
            }
            if (!Schema::hasColumn('wallet_transactions', 'related_type')) {
                $table->string('related_type')->nullable()->after('description');
            }
            if (!Schema::hasColumn('wallet_transactions', 'related_id')) {
                $table->unsignedBigInteger('related_id')->nullable()->after('related_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $columns = ['order_id', 'order_type', 'performed_by', 'description', 'related_type', 'related_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('wallet_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
