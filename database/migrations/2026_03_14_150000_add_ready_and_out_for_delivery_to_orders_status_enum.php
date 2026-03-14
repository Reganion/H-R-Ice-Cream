<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Full enum list: Pending, Preparing, Assigned, Completed, Cancelled, Walk-in, Ready, Out for Delivery */
    private const STATUSES = "'pending','Preparing','Walk-in','Assigned','Completed','Cancelled','Ready','Out for Delivery'";

    public function up(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(" . self::STATUSES . ") NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        // Map new statuses back to existing before shrinking enum
        DB::statement("UPDATE orders SET status = 'Assigned' WHERE LOWER(TRIM(status)) IN ('ready', 'out for delivery')");
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','Preparing','Walk-in','Assigned','Completed','Cancelled') NOT NULL DEFAULT 'pending'");
    }
};
