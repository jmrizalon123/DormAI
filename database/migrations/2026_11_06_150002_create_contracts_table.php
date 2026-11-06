<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_space_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('previous_contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->string('contract_number', 40);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('monthly_rate', 12, 2);
            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->decimal('advance_payment', 12, 2)->default(0);
            $table->longText('terms');
            $table->string('status', 32)->default('active');
            $table->string('pdf_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'contract_number']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
