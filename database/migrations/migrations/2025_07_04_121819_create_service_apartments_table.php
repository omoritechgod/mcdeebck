<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');

            $table->string('full_name');
            $table->string('phone_number');
            $table->string('organization_name')->nullable(); // For business vendors
            $table->string('organization_address')->nullable();
            $table->string('website')->nullable();
            $table->string('years_of_experience')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_apartments');
    }
};
