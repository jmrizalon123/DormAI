<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('utility_reading_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('consumption_units', 14, 3);
            $table->decimal('rate_per_unit', 12, 4);
            $table->decimal('amount', 12, 2);
            $table->string('status', 32)->default('calculated');
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'utility_reading_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_allocations');
    }
};
