<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile', 11)->nullable()->unique()->after('email');
            $table->timestamp('mobile_verified_at')->nullable()->after('mobile');
            $table->string('status')->default('active')->after('mobile_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->dropColumn([
                'mobile',
                'mobile_verified_at',
                'status',
                'last_login_at',
            ]);
        });
    }
};