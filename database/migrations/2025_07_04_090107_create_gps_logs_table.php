<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
    {
        Schema::create('gps_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained()->onDelete('cascade');
            $table->decimal('lat', 10, 6);
            $table->decimal('lng', 10, 6);
            $table->timestamp('logged_at')->useCurrent();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_logs');
    }
};
