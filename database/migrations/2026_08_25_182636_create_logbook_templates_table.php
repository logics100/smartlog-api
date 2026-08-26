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
       Schema::create('logbook_templates', function (Blueprint $table) {
    $table->id();

    $table->foreignId('unit_id')
          ->constrained('units')
          ->cascadeOnDelete();

    $table->string('template_name', 200);

    $table->text('description')->nullable();

    $table->unsignedInteger('minimum_completion_percentage')
          ->nullable();

    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_templates');
    }
};
