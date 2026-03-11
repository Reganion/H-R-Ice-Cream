<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_messages')) {
            return;
        }

        Schema::create('order_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('driver_id');
            $table->integer('customer_id');
            $table->string('sender_type', 20); // 'driver' | 'customer'
            $table->text('message');
            $table->enum('status', ['active', 'archive'])->default('active');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('driver_id');
            $table->index('customer_id');
            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_messages');
    }
};
