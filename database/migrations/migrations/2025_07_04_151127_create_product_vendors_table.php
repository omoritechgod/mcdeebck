<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');

            $table->string('contact_person');
            $table->string('store_address');
            $table->string('store_phone');
            $table->string('store_email')->nullable();
            $table->text('store_description')->nullable();
            $table->string('logo')->nullable(); // optional branding

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_vendors');
    }
};
