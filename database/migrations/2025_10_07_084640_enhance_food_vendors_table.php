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
        Schema::table('food_vendors', function (Blueprint $table) {
            $table->json('operating_hours')->nullable()->after('description');
            $table->decimal('delivery_radius_km', 5, 2)->default(5.00)->after('operating_hours');
            $table->decimal('minimum_order_amount', 10, 2)->default(0)->after('delivery_radius_km');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('minimum_order_amount');
            $table->boolean('is_open')->default(true)->after('delivery_fee');
            $table->boolean('accepts_cash')->default(true)->after('is_open');
            $table->boolean('accepts_card')->default(true)->after('accepts_cash');
            $table->decimal('average_rating', 3, 2)->default(0)->after('accepts_card');
            $table->integer('total_reviews')->default(0)->after('average_rating');
            $table->integer('total_orders')->default(0)->after('total_reviews');
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->json('cuisines')->nullable()->after('specialty');
            $table->integer('estimated_preparation_time')->default(30)->after('cuisines');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_vendors', function (Blueprint $table) {
            $table->dropColumn([
                'operating_hours',
                'delivery_radius_km',
                'minimum_order_amount',
                'delivery_fee',
                'is_open',
                'accepts_cash',
                'accepts_card',
                'average_rating',
                'total_reviews',
                'total_orders',
                'latitude',
                'longitude',
                'cuisines',
                'estimated_preparation_time'
            ]);
        });
    }
};
