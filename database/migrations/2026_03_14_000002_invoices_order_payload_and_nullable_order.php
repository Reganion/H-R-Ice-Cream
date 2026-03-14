<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - order_id nullable: order is created only after downpayment is paid.
     * - order_payload: stores order data until payment succeeds.
     * - customer_id: ownership when invoice has no order yet.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'order_payload')) {
                $table->json('order_payload')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('invoices', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('order_payload');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->change();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable(false)->change();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['order_payload', 'customer_id']);
        });
    }
};
