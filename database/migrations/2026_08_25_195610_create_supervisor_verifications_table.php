<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clinical_entry_id')
                  ->nullable()
                  ->constrained('clinical_entries')
                  ->cascadeOnDelete();

            $table->foreignId('attendance_record_id')
                  ->nullable()
                  ->constrained('attendance_records')
                  ->cascadeOnDelete();

            $table->foreignId('clinical_supervisor_id')
                  ->nullable()
                  ->constrained('clinical_supervisors')
                  ->nullOnDelete();

            $table->string('supervisor_name', 150);

            $table->string('facility_name', 200)->nullable();

            $table->string('signature_path')->nullable();

            $table->string('face_capture_path')->nullable();

            $table->decimal('face_match_score', 5, 4)->nullable();

            $table->boolean('face_match_passed')->nullable();

            $table->timestamp('activity_timestamp')->nullable();

            $table->timestamp('verification_timestamp')->nullable();

            $table->boolean('time_check_passed')->nullable();

            $table->enum('verification_status', [
                'PENDING',
                'APPROVED',
                'REJECTED',
                'MANUAL_REVIEW'
            ])->default('PENDING');

            $table->string('verification_method', 100)
                  ->default('STYLUS_FACE_TIME');

            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_verifications');
    }
};