<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL because Laravel's schema builder doesn't fully support enum alterations
        DB::statement("
            ALTER TABLE orders 
            MODIFY status ENUM(
                'pending_vendor',   -- waiting for vendor confirmation
                'awaiting_payment', -- user needs to pay
                'paid',             -- escrow funded
                'processing',       -- vendor preparing order
                'shipped',          -- dispatched
                'completed',        -- finished, funds released
                'cancelled'         -- rejected/cancelled
            ) NOT NULL DEFAULT 'pending_vendor'
        ");
    }

    public function down(): void
    {
        // Revert to a minimal set if you roll back
        DB::statement("
            ALTER TABLE orders 
            MODIFY status ENUM(
                'pending',
                'paid',
                'processing',
                'shipped',
                'completed',
                'cancelled'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};
