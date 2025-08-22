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
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('is_setup_complete')->default(false)->after('category');
        });
    }

    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('is_setup_complete');
        });
    }

};
