<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->enum('vendor_type', ['individual', 'business']);
            $table->string('business_name')->nullable();

            $table->enum('category', [
                'rider',
                'mechanic',
                'apartment',
                'product_vendor',
                'service_vendor'
            ])->comment('Specifies the business category of the vendor');

            $table->boolean('is_verified')->default(false); // Only verified vendors can go live

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
