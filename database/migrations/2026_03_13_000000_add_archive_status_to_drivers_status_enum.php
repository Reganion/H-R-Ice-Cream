<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE drivers MODIFY COLUMN status ENUM('available','on_route','off_duty','deactivate','archive') NOT NULL DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        DB::table('drivers')
            ->where('status', 'archive')
            ->update(['status' => 'deactivate']);

        DB::statement("ALTER TABLE drivers MODIFY COLUMN status ENUM('available','on_route','off_duty','deactivate') NOT NULL DEFAULT 'available'");
    }
};
