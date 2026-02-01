<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('flavors')) {
            return;
        }
        Schema::create('flavors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('flavor_type');
            $table->string('category');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flavors');
    }
};
