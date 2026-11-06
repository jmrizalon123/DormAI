<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_request_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event');
            $table->text('message');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'work_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
