<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_messages') || Schema::hasColumn('order_messages', 'status')) {
            return;
        }

        Schema::table('order_messages', function (Blueprint $table) {
            $table->enum('status', ['active', 'archive'])->default('active')->after('message');
            $table->index(['order_id', 'status']);
        });

        DB::table('order_messages')->update(['status' => 'active']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_messages') || !Schema::hasColumn('order_messages', 'status')) {
            return;
        }

        Schema::table('order_messages', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
