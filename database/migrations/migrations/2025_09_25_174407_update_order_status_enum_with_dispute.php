<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE orders 
            MODIFY status ENUM(
                'pending_vendor',   -- waiting for vendor confirmation
                'awaiting_payment', -- user needs to pay
                'paid',             -- escrow funded
                'processing',       -- vendor preparing order
                'shipped',          -- dispatched
                'completed',        -- finished, funds released
                'cancelled',        -- rejected/cancelled
                'disputed',         -- admin intervention needed
                'refunded'          -- refunded to user
            ) NOT NULL DEFAULT 'pending_vendor'
        ");
    }

    public function down(): void
    {
        // Roll back to previous set (without dispute/refund)
        DB::statement("
            ALTER TABLE orders 
            MODIFY status ENUM(
                'pending_vendor',
                'awaiting_payment',
                'paid',
                'processing',
                'shipped',
                'completed',
                'cancelled'
            ) NOT NULL DEFAULT 'pending_vendor'
        ");
    }
};
