<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        if ($this->hasCustomerForeignKey()) {
            DB::statement('ALTER TABLE `orders` DROP FOREIGN KEY `orders_customer_id_foreign`');
        }

        if (!Schema::hasColumn('orders', 'customer_id')) {
            Schema::table('orders', function (Blueprint $table) {
                // Live DB uses INT for customers.id, so customer_id must match.
                $table->integer('customer_id')->nullable()->after('id');
            });
        } else {
            DB::statement('ALTER TABLE `orders` MODIFY `customer_id` INT NULL');
        }

        // Best-effort backfill for existing data using phone first, then full name.
        DB::table('orders')
            ->whereNull('customer_id')
            ->orderBy('id')
            ->chunkById(200, function ($orders): void {
                foreach ($orders as $order) {
                    $customerId = null;

                    if (!empty($order->customer_phone)) {
                        $customerId = DB::table('customers')
                            ->where('contact_no', $order->customer_phone)
                            ->value('id');
                    }

                    if ($customerId === null && !empty($order->customer_name)) {
                        $customerId = DB::table('customers')
                            ->whereRaw("TRIM(CONCAT(firstname, ' ', lastname)) = ?", [$order->customer_name])
                            ->value('id');
                    }

                    if ($customerId !== null) {
                        DB::table('orders')
                            ->where('id', $order->id)
                            ->update(['customer_id' => $customerId]);
                    }
                }
            });

        // Remove any non-matching values before adding FK.
        DB::statement(
            "UPDATE `orders` o
             LEFT JOIN `customers` c ON c.`id` = o.`customer_id`
             SET o.`customer_id` = NULL
             WHERE o.`customer_id` IS NOT NULL
               AND c.`id` IS NULL"
        );

        if (!$this->hasCustomerForeignKey()) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreign('customer_id')
                    ->references('id')
                    ->on('customers')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'customer_id')) {
            return;
        }

        if ($this->hasCustomerForeignKey()) {
            DB::statement('ALTER TABLE `orders` DROP FOREIGN KEY `orders_customer_id_foreign`');
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });
    }

    private function hasCustomerForeignKey(): bool
    {
        $result = DB::selectOne(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'orders'
               AND COLUMN_NAME = 'customer_id'
               AND REFERENCED_TABLE_NAME IS NOT NULL
               AND CONSTRAINT_NAME = 'orders_customer_id_foreign'
             LIMIT 1"
        );

        return $result !== null;
    }
};
