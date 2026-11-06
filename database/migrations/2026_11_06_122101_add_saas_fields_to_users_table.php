<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('account_type', 32)->default('staff')->after('email');
            $table->string('status', 32)->default('active')->after('account_type');
            $table->string('phone', 30)->nullable()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');

            $table->index(['organization_id', 'status']);
            $table->index(['account_type', 'status']);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn([
                'organization_id',
                'account_type',
                'status',
                'phone',
                'last_login_at',
            ]);
        });
    }
};
