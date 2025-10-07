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
            if (!Schema::hasColumn('riders', 'is_available')) {
                $table->boolean('is_available')->default(false)->after('status');
            }
            if (!Schema::hasColumn('riders', 'current_latitude')) {
                $table->string('current_latitude')->nullable()->after('is_available');
            }
            if (!Schema::hasColumn('riders', 'current_longitude')) {
                $table->string('current_longitude')->nullable()->after('current_latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $columns = ['is_available', 'current_latitude', 'current_longitude'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('riders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
