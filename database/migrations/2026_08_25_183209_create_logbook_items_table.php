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
       Schema::create('logbook_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('logbook_section_id')
          ->constrained('logbook_sections')
          ->cascadeOnDelete();

    $table->string('item_name', 255);

    $table->text('item_description')->nullable();

    $table->string('required_level', 50)->nullable();

    $table->unsignedInteger('required_count')
          ->nullable();

    $table->boolean('requires_supervisor_verification')
          ->default(false);

    $table->unsignedInteger('display_order')->default(1);

    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_items');
    }
};
