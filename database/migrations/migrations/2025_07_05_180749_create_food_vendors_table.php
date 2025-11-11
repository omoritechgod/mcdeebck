<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoodVendorsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('food_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->string('specialty');
            $table->string('location');
            $table->string('contact_phone');
            $table->string('contact_email')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable(); // You can update to use file path or base64
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_vendors');
    }
}
