<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location');
            $table->decimal('price_per_night', 10, 2);
            $table->enum('type', ['hostel', 'hotel', 'shortlet']);
            $table->json('images')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
