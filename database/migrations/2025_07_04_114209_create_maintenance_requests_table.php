<?php
// database/migrations/xxxx_xx_xx_create_maintenance_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('location');
            $table->string('service_type');
            $table->text('issue');
            $table->boolean('needs_towing')->default(false);
            $table->enum('status', ['pending', 'accepted', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('mechanic_id')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
