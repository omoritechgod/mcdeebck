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
            $table->boolean('is_available')->default(false)->after('status');
            $table->string('current_latitude')->nullable()->after('is_available');
            $table->string('current_longitude')->nullable()->after('current_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn(['is_available', 'current_latitude', 'current_longitude']);
        });
    }
};
