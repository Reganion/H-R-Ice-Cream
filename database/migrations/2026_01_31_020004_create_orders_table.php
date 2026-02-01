<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            return;
        }
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('product_name');
            $table->string('product_type');
            $table->string('gallon_size', 50);
            $table->string('product_image')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_image')->nullable();
            $table->date('delivery_date');
            $table->string('delivery_time');
            $table->string('delivery_address');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
