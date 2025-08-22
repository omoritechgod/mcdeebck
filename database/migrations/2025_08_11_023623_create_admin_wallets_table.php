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
        Schema::create('admin_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Main Company Wallet');
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('currency')->default('NGN');
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_wallets');
    }
};
