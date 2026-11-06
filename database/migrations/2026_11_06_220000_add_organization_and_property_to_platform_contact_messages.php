<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_contact_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('platform_contact_messages', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('platform_contact_messages', 'property_id')) {
                $table->foreignId('property_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('platform_contact_messages', function (Blueprint $table) {
            if (Schema::hasColumn('platform_contact_messages', 'property_id')) {
                $table->dropForeign(['property_id']);
                $table->dropColumn('property_id');
            }
            if (Schema::hasColumn('platform_contact_messages', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
        });
    }
};
