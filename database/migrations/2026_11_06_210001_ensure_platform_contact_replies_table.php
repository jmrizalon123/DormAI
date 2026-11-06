<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('platform_contact_replies')) {
            return;
        }

        Schema::create('platform_contact_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_message_id')->constrained('platform_contact_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_contact_replies');
    }
};
