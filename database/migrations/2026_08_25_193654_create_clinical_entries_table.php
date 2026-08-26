<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_logbook_id')
                  ->constrained('student_logbooks')
                  ->cascadeOnDelete();

            $table->foreignId('logbook_item_id')
                  ->nullable()
                  ->constrained('logbook_items')
                  ->nullOnDelete();

            $table->foreignId('logbook_item_requirement_id')
                  ->nullable()
                  ->constrained('logbook_item_requirements')
                  ->nullOnDelete();

            $table->date('activity_date');

            $table->time('activity_time')->nullable();

            $table->string('facility_name', 200)->nullable();

            $table->string('clinical_area', 200)->nullable();

            $table->text('activity_details')->nullable();

            $table->string('competency_level', 50)->nullable();

            $table->enum('status', [
                'DRAFT',
                'PENDING_VERIFICATION',
                'VERIFIED',
                'REJECTED'
            ])->default('DRAFT');

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
        Schema::dropIfExists('clinical_entries');
    }
};