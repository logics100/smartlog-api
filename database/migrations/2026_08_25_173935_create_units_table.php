<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('units', function (Blueprint $table) {
    $table->id();

    $table->string('unit_code', 30)->nullable();
    $table->string('unit_name', 150);

    $table->foreignId('department_id')
          ->constrained('departments')
          ->cascadeOnDelete();

    $table->foreignId('year_level_id')
          ->constrained('year_levels')
          ->cascadeOnDelete();

    $table->foreignId('semester_id')
          ->constrained('semesters')
          ->cascadeOnDelete();

    $table->boolean('requires_logbook')->default(true);
    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
