<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_space_id')->constrained()->cascadeOnDelete();
            $table->date('move_in_date');
            $table->date('move_out_date')->nullable();
            $table->string('status', 32)->default('active');
            $table->string('ended_reason')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'tenant_id', 'status']);
            $table->index(['bed_space_id', 'status']);
        });

        Schema::create('tenant_assignment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bed_space_id')->nullable()->constrained()->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_assignment_history');
        Schema::dropIfExists('tenant_assignments');
    }
};
