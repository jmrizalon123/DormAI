<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('platform_contact_messages') && Schema::hasTable('platform_contact_replies')) {
            return;
        }

        if (! Schema::hasTable('platform_contact_messages')) {
            Schema::create('platform_contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 32)->unique();
                $table->string('name');
                $table->string('email');
                $table->string('phone', 40)->nullable();
                $table->string('subject');
                $table->text('body');
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status', 32)->default('pending');
                $table->timestamp('read_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index('email');
            });
        }

        if (! Schema::hasTable('platform_contact_replies')) {
            Schema::create('platform_contact_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contact_message_id')->constrained('platform_contact_messages')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_contact_replies');
        Schema::dropIfExists('platform_contact_messages');
    }
};
