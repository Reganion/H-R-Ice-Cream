<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feedback')) {
            return;
        }
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('photo')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('testimonial')->nullable();
            $table->date('feedback_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
