<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            // Links to customers.id (logical FK; no DB constraint to avoid engine/charset issues)
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('contact_no', 20)->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('barangay')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('street_name')->nullable();
            $table->string('label_as')->nullable();
            $table->text('reason')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
