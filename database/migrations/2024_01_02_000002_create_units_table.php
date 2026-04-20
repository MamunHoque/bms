<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('unit_number');
            $table->unsignedSmallInteger('floor')->default(1);
            $table->unsignedTinyInteger('bedrooms')->default(1);
            $table->unsignedTinyInteger('bathrooms')->default(1);
            $table->unsignedInteger('size_sqft')->nullable();
            $table->decimal('base_rent', 12, 2)->default(0);
            $table->string('status')->default('vacant'); // vacant, occupied, maintenance
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['building_id', 'unit_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
