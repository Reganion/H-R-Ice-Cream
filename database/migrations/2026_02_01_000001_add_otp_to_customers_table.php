<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('otp', 10)->nullable()->after('password');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
            $table->timestamp('email_verified_at')->nullable()->after('otp_expires_at');
        });

        DB::table('customers')->whereNull('email_verified_at')->update(['email_verified_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at', 'email_verified_at']);
        });
    }
};
