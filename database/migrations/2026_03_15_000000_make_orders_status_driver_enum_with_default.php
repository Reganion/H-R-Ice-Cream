<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ENUM_VALUES = "'Pending','Accepted','Completed'";

    /**
     * Run the migrations.
     * Change status_driver to ENUM(Pending, Accepted, Completed) NOT NULL DEFAULT 'Pending'.
     */
    public function up(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        // Normalize existing values before altering: null/empty -> Pending, accepted/on_route -> Accepted, completed -> Completed
        DB::statement("UPDATE orders SET status_driver = 'Pending' WHERE status_driver IS NULL OR TRIM(status_driver) = ''");
        DB::statement("UPDATE orders SET status_driver = 'Accepted' WHERE LOWER(TRIM(status_driver)) IN ('accepted', 'on_route')");
        DB::statement("UPDATE orders SET status_driver = 'Completed' WHERE LOWER(TRIM(status_driver)) = 'completed'");
        DB::statement("UPDATE orders SET status_driver = 'Pending' WHERE status_driver NOT IN ('Pending', 'Accepted', 'Completed')");

        DB::statement("ALTER TABLE orders MODIFY COLUMN status_driver ENUM(" . self::ENUM_VALUES . ") NOT NULL DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY COLUMN status_driver VARCHAR(255) NULL");
    }
};
