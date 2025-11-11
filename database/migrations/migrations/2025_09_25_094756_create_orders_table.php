<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // buyer
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // vendor (for marketplace we assume one vendor per order – cart must group by vendor)
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            // payment & delivery
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', [
                'pending',     // order created, not paid
                'paid',        // payment confirmed (escrow)
                'processing',  // vendor preparing
                'shipped',     // vendor marked as shipped
                'delivered',   // vendor marked delivered
                'completed',   // user confirmed completion, escrow released
                'disputed',    // user raised dispute
                'cancelled'    // admin cancelled/refunded
            ])->default('pending');

            $table->string('delivery_address')->nullable();
            $table->string('delivery_method')->nullable(); // e.g. pickup/shipping

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
