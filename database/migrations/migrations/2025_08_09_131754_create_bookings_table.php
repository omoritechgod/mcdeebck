<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('nights');
            $table->decimal('total_price', 12, 2);
            $table->enum('status', [
                'pending',       // Submitted by user, waiting admin
                'processing',    // Admin approved, waiting payment
                'paid',          // Payment done
                'checked_in',    // User checked in
                'checked_out',   // User checked out
                'completed',     // Vendor paid
                'cancelled',     // Booking cancelled
                'refunded'       // Refund processed
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
