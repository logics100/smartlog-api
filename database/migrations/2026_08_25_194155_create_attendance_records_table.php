<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_logbook_id')
                  ->constrained('student_logbooks')
                  ->cascadeOnDelete();

            $table->date('attendance_date');

            $table->string('facility_name', 200);

            $table->string('clinical_unit', 200)->nullable();

            $table->time('start_time');

            $table->time('finish_time');

            $table->decimal('total_hours', 5, 2)->nullable();

            $table->enum('status', [
                'PENDING_VERIFICATION',
                'VERIFIED',
                'REJECTED'
            ])->default('PENDING_VERIFICATION');

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
        Schema::dropIfExists('attendance_records');
    }
};