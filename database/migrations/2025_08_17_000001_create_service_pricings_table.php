<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_pricings', function (Blueprint $table) {
            $table->id();
   $table->foreignId('service_vendor_id')
          ->constrained('service_vendors')
          ->onDelete('cascade');
            $table->string('title');               // e.g. "AC Repair"
            $table->decimal('price', 12, 2);       // e.g. 50000.00
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('service_pricings');
    }
};
