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
        Schema::create('admin_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_wallet_id');
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);
            $table->string('ref')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->json('meta')->nullable(); // For extra data (booking id, user id, etc.)
            $table->timestamps();

            $table->foreign('admin_wallet_id')
                ->references('id')
                ->on('admin_wallets')
                ->onDelete('cascade');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_transactions');
    }
};
