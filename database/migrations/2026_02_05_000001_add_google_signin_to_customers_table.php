<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add support for Google Sign-in: google_id for linking accounts,
     * password nullable for Google-only users.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('google_id', 255)->nullable()->unique()->after('email');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });
    }
};
