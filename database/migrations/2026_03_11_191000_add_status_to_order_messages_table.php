<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ORDER_STATUS_INDEX = 'order_messages_order_id_status_index';

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            "SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = ?
               AND table_name = ?
               AND index_name = ?
             LIMIT 1",
            [$database, $table, $indexName]
        );

        return $row !== null;
    }

    public function up(): void
    {
        if (!Schema::hasTable('order_messages')) {
            return;
        }

        if (!Schema::hasColumn('order_messages', 'status')) {
            Schema::table('order_messages', function (Blueprint $table) {
                $table->enum('status', ['active', 'archive'])->default('active')->after('message');
            });
        }

        if (!$this->indexExists('order_messages', self::ORDER_STATUS_INDEX)) {
            Schema::table('order_messages', function (Blueprint $table) {
                $table->index(['order_id', 'status'], self::ORDER_STATUS_INDEX);
            });
        }

        DB::table('order_messages')->update(['status' => 'active']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_messages') || !Schema::hasColumn('order_messages', 'status')) {
            return;
        }

        Schema::table('order_messages', function (Blueprint $table) {
            if (Schema::hasColumn('order_messages', 'status') && $this->indexExists('order_messages', self::ORDER_STATUS_INDEX)) {
                $table->dropIndex(self::ORDER_STATUS_INDEX);
            }
            $table->dropColumn('status');
        });
    }
};
