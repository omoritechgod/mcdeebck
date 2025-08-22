<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignId('service_pricing_id')
                ->nullable()
                ->after('service_vendor_id')
                ->constrained('service_pricings')
                ->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_pricing_id');
        });
    }
};
