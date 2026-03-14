<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_messages')) {
            return;
        }

        if (!Schema::hasColumn('order_messages', 'customer_status')) {
            Schema::table('order_messages', function (Blueprint $table) {
                $table->string('customer_status', 20)->default('active')->after('message');
            });
            DB::table('order_messages')->update(['customer_status' => 'active']);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_messages') || !Schema::hasColumn('order_messages', 'customer_status')) {
            return;
        }

        Schema::table('order_messages', function (Blueprint $table) {
            $table->dropColumn('customer_status');
        });
    }
};
