<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Electricity, Water, Gas, Service Charge
            $table->string('unit_label')->nullable(); // kWh, gallons, cft
            $table->decimal('rate_per_unit', 10, 4)->default(0);
            $table->decimal('flat_fee', 12, 2)->default(0);
            $table->boolean('is_metered')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('utility_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('utility_type_id')->constrained()->cascadeOnDelete();
            $table->date('period_month'); // first day of the billing month
            $table->decimal('previous_reading', 12, 2)->default(0);
            $table->decimal('current_reading', 12, 2)->default(0);
            $table->decimal('consumption', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['unit_id', 'utility_type_id', 'period_month'], 'ur_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_readings');
        Schema::dropIfExists('utility_types');
    }
};
