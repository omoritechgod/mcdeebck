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
        Schema::table('riders', function (Blueprint $table) {
           $table->enum('availability', ['online', 'offline'])->default('offline')->after('status');
            $table->decimal('current_lat', 10, 6)->nullable()->after('availability');
            $table->decimal('current_lng', 10, 6)->nullable()->after('current_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn(['availability', 'current_lat', 'current_lng']);
        });
    }
};
