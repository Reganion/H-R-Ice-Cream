<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STATUSES = "'pending','Walk-in','Assigned','Completed','Cancelled'";

    public function up(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        // Normalize existing values so they fit the new enum.
        DB::statement("
            UPDATE orders
            SET status = CASE
                WHEN status IS NULL OR TRIM(status) = '' THEN 'pending'
                WHEN LOWER(TRIM(status)) IN ('pending', 'new') THEN 'pending'
                WHEN LOWER(TRIM(status)) IN ('walk-in', 'walk_in', 'walk in', 'walkin') THEN 'Walk-in'
                WHEN LOWER(TRIM(status)) = 'assigned' THEN 'Assigned'
                WHEN LOWER(TRIM(status)) IN ('completed', 'delivered') THEN 'Completed'
                WHEN LOWER(TRIM(status)) = 'cancelled' THEN 'Cancelled'
                ELSE 'pending'
            END
        ");

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(" . self::STATUSES . ") NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'pending'");
    }
};
