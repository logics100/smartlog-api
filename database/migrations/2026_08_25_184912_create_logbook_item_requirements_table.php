<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_item_requirements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('logbook_item_id')
                  ->constrained('logbook_items')
                  ->cascadeOnDelete();

            $table->unsignedTinyInteger('sequence_number');

            $table->string('requirement_code', 20);

            $table->string('requirement_label', 100)->nullable();

            $table->boolean('is_simulation')->default(false);

            $table->timestamps();

            $table->unique([
                'logbook_item_id',
                'sequence_number'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_item_requirements');
    }
};