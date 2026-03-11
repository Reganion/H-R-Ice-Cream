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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('received_amount', 12, 2)->nullable()->after('amount');
            $table->string('delivery_payment_method', 50)->nullable()->after('received_amount');
            $table->string('delivery_proof_image')->nullable()->after('delivery_payment_method');
            $table->timestamp('delivered_at')->nullable()->after('delivery_proof_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'received_amount',
                'delivery_payment_method',
                'delivery_proof_image',
                'delivered_at',
            ]);
        });
    }
};
