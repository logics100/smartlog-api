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
        Schema::create('student_logbooks', function (Blueprint $table) {
    $table->id();

    $table->foreignId('enrollment_id')
          ->constrained('enrollments')
          ->cascadeOnDelete();

    $table->foreignId('logbook_template_id')
          ->constrained('logbook_templates')
          ->cascadeOnDelete();

    $table->date('assigned_date');

    $table->date('due_date')->nullable();

    $table->enum('status', [
        'ACTIVE',
        'SUBMITTED',
        'COMPLETED',
        'ARCHIVED'
    ])->default('ACTIVE');

    $table->decimal('completion_percentage', 5, 2)
          ->default(0);

    $table->timestamps();

    $table->unique([
        'enrollment_id',
        'logbook_template_id'
    ]);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_logbooks');
    }
};
