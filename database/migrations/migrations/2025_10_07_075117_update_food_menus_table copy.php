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
            $table->string('slug')->nullable()->after('name');
            $table->integer('preparation_time_minutes')->default(30)->after('price');
            $table->string('category')->nullable()->after('preparation_time_minutes');
            $table->boolean('is_available')->default(true)->after('category');
            $table->json('image_urls')->nullable()->after('image');
            $table->json('tags')->nullable()->after('image_urls');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_menus', function (Blueprint $table) {
            $table->dropColumn(['slug', 'preparation_time_minutes', 'category', 'is_available', 'image_urls', 'tags']);
        });
    }
};
