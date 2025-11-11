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
        Schema::create('food_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('preparation_time_minutes')->default(15);
            $table->string('category')->nullable(); // e.g., 'appetizer', 'main', 'dessert', 'drinks'
            $table->boolean('is_available')->default(true);
            $table->json('image_urls')->nullable(); // Array of Cloudinary URLs
            $table->integer('stock')->nullable(); // Optional for limited items
            $table->json('tags')->nullable(); // e.g., ['spicy', 'vegan', 'gluten-free']
            $table->timestamps();
            
            $table->index('vendor_id');
            $table->index('category');
            $table->index('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_menu_items');
    }
};