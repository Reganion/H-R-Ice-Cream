<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Status values for enum (must match Driver model constants) */
    private const STATUSES = "'available','on_route','off_duty','deactivate'";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }
        // Normalize any existing invalid status values before altering to enum
        $valid = ['available', 'on_route', 'off_duty', 'deactivate'];
        DB::table('drivers')
            ->whereNotIn('status', $valid)
            ->update(['status' => 'available']);
        DB::statement("ALTER TABLE drivers MODIFY COLUMN status ENUM(" . self::STATUSES . ") NOT NULL DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE drivers MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'available'");
    }
};
