<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE products
            MODIFY status ENUM(
                'draft',    -- product saved but not public
                'active',   -- visible in marketplace (if vendor is live)
                'inactive'  -- manually disabled
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        // Rollback: allow a more basic set
        DB::statement("
            ALTER TABLE products
            MODIFY status ENUM(
                'active',
                'inactive'
            ) NOT NULL DEFAULT 'active'
        ");
    }
};
