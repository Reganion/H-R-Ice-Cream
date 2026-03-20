<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'fcm_token')) {
                $table->string('fcm_token', 2048)->nullable()->after('reason');
            }
            if (!Schema::hasColumn('customers', 'fcm_platform')) {
                $table->string('fcm_platform', 20)->nullable()->after('fcm_token');
            }
        });

        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'fcm_token')) {
                $table->string('fcm_token', 2048)->nullable()->after('last_updated');
            }
            if (!Schema::hasColumn('drivers', 'fcm_platform')) {
                $table->string('fcm_platform', 20)->nullable()->after('fcm_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'fcm_platform')) {
                $table->dropColumn('fcm_platform');
            }
            if (Schema::hasColumn('customers', 'fcm_token')) {
                $table->dropColumn('fcm_token');
            }
        });

        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'fcm_platform')) {
                $table->dropColumn('fcm_platform');
            }
            if (Schema::hasColumn('drivers', 'fcm_token')) {
                $table->dropColumn('fcm_token');
            }
        });
    }
};

