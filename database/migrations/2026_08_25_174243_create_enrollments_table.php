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
        Schema::create('enrollments', function (Blueprint $table) {
    $table->id();

    $table->foreignId('student_id')
          ->constrained('users')
          ->cascadeOnDelete();

    $table->foreignId('unit_id')
          ->constrained('units')
          ->cascadeOnDelete();

    $table->foreignId('enrolled_by')
          ->constrained('users')
          ->cascadeOnDelete();

    $table->date('enrollment_date');

    $table->enum('status', [
        'ACTIVE',
        'COMPLETED',
        'WITHDRAWN'
    ])->default('ACTIVE');

    $table->timestamps();

    $table->unique([
        'student_id',
        'unit_id'
    ]);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
