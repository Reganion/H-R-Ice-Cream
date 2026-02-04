<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('province')->nullable()->after('email_verified_at');
            $table->string('city')->nullable()->after('province');
            $table->string('barangay')->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('barangay');
            $table->string('street_name')->nullable()->after('postal_code');
            $table->string('label_as')->nullable()->after('street_name');
            $table->text('reason')->nullable()->after('label_as');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'province',
                'city',
                'barangay',
                'postal_code',
                'street_name',
                'label_as',
                'reason',
            ]);
        });
    }
};
