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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Vendor that owns the product
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            // Product details
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('images')->nullable(); // store array of image URLs/paths

            $table->decimal('price', 12, 2);
            $table->integer('stock_quantity')->default(0);

            // Category (assuming you already have categories table)
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');

            $table->enum('condition', ['new', 'used'])->default('new');

            // Delivery options
            $table->boolean('allow_pickup')->default(false);
            $table->boolean('allow_shipping')->default(false);

            // Status (published/unpublished/hidden, for admin control if needed)
            $table->enum('status', ['draft', 'active', 'inactive'])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
