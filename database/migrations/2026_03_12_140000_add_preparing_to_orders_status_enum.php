<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STATUSES_WITH_PREPARING = "'pending','Walk-in','Preparing','Assigned','Completed','Cancelled'";
    private const STATUSES_WITHOUT_PREPARING = "'pending','Walk-in','Assigned','Completed','Cancelled'";

    public function up(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(" . self::STATUSES_WITH_PREPARING . ") NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        // Map preparing back to pending before removing enum value.
        DB::statement("UPDATE orders SET status = 'pending' WHERE LOWER(TRIM(status)) = 'preparing'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(" . self::STATUSES_WITHOUT_PREPARING . ") NOT NULL DEFAULT 'pending'");
    }
};

