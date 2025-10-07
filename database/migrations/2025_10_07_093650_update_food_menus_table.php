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
            if (!Schema::hasColumn('food_menus', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('food_menus', 'preparation_time_minutes')) {
                $table->integer('preparation_time_minutes')->default(30)->after('price');
            }
            if (!Schema::hasColumn('food_menus', 'category')) {
                $table->string('category')->nullable()->after('preparation_time_minutes');
            }
            if (!Schema::hasColumn('food_menus', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('category');
            }
            if (!Schema::hasColumn('food_menus', 'image_urls')) {
                $table->json('image_urls')->nullable()->after('image');
            }
            if (!Schema::hasColumn('food_menus', 'tags')) {
                $table->json('tags')->nullable()->after('image_urls');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_menus', function (Blueprint $table) {
            $columns = ['slug', 'preparation_time_minutes', 'category', 'is_available', 'image_urls', 'tags'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('food_menus', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
