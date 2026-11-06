<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('utility_meter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->date('reading_date');
            $table->decimal('previous_reading', 14, 3)->default(0);
            $table->decimal('current_reading', 14, 3);
            $table->decimal('consumption', 14, 3);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'reading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_readings');
    }
};
