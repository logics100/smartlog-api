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
        Schema::create('logbook_sections', function (Blueprint $table) {
    $table->id();

    $table->foreignId('logbook_template_id')
          ->constrained('logbook_templates')
          ->cascadeOnDelete();

    $table->string('section_title', 200);

    $table->enum('section_type', [
        'ATTENDANCE',
        'PROCEDURE',
        'CHECKLIST',
        'PATIENT_LOG',
        'CASELOAD',
        'SKILLS',
        'REFLECTION',
        'ASSESSMENT',
        'GENERAL'
    ])->default('GENERAL');

    $table->text('instructions')->nullable();

    $table->unsignedInteger('display_order')->default(1);

    $table->boolean('requires_supervisor_verification')
          ->default(false);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_sections');
    }
};
