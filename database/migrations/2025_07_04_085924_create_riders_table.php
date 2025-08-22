<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');

            $table->enum('vehicle_type', ['bike', 'tricycle', 'car'])->nullable();
            $table->string('license_number')->nullable();
            $table->integer('experience_years')->nullable();

            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};
