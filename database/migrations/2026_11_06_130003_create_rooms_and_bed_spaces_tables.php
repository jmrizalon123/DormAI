<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('room_number', 32);
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->decimal('monthly_rate', 12, 2)->default(0);
            $table->string('status', 32)->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['floor_id', 'room_number']);
            $table->index(['organization_id', 'property_id', 'status']);
        });

        Schema::create('room_room_amenity', function (Blueprint $table) {
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_amenity_id')->constrained()->cascadeOnDelete();
            $table->primary(['room_id', 'room_amenity_id']);
        });

        Schema::create('bed_spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('label', 32);
            $table->decimal('monthly_rate', 12, 2)->nullable();
            $table->string('status', 32)->default('available');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['room_id', 'label']);
            $table->index(['room_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bed_spaces');
        Schema::dropIfExists('room_room_amenity');
        Schema::dropIfExists('rooms');
    }
};
