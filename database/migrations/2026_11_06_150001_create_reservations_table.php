<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_space_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_code', 32);
            $table->string('applicant_first_name');
            $table->string('applicant_last_name');
            $table->string('applicant_email');
            $table->string('applicant_phone', 30)->nullable();
            $table->date('requested_check_in');
            $table->date('requested_check_out')->nullable();
            $table->string('status', 32)->default('pending');
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->timestamp('deposit_paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('applicant_notes')->nullable();
            $table->text('staff_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'reference_code']);
            $table->index(['organization_id', 'status', 'created_at']);
            $table->index(['bed_space_id', 'status']);
        });

        Schema::create('reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 32);
            $table->string('description');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_items');
        Schema::dropIfExists('reservations');
    }
};
