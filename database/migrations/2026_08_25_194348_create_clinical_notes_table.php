<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('unit_id')
                  ->nullable()
                  ->constrained('units')
                  ->nullOnDelete();

            $table->string('title', 200)->nullable();

            $table->text('note_text');

            $table->boolean('is_private')->default(true);

            $table->boolean('created_offline')->default(false);

            $table->enum('sync_status', [
                'LOCAL',
                'PENDING',
                'SYNCED',
                'FAILED'
            ])->default('SYNCED');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};