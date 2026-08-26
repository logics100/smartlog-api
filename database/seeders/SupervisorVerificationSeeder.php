<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupervisorVerificationSeeder extends Seeder
{
    public function run(): void
    {
        // Get the most recently created clinical entry.
        $entry = DB::table('clinical_entries')
            ->latest('id')
            ->first();

        if (!$entry) {
            $this->command->error('No clinical entry found.');
            return;
        }

        // Get our test clinical supervisor.
        $supervisor = DB::table('clinical_supervisors')
            ->where('registration_number', 'TEST-SUP-001')
            ->first();

        if (!$supervisor) {
            $this->command->error('Test clinical supervisor not found.');
            return;
        }

        // Create the supervisor verification record.
        $verificationId = DB::table('supervisor_verifications')
            ->insertGetId([

                'clinical_entry_id' => $entry->id,

                'attendance_record_id' => null,

                'clinical_supervisor_id' => $supervisor->id,

                'supervisor_name' => $supervisor->full_name,

                'facility_name' => $supervisor->facility_name,

                // Temporary test files.
                // Flutter will capture real files later.
                'signature_path' =>
                    'test/signatures/test_signature.png',

                'face_capture_path' =>
                    'test/faces/test_face.jpg',

                // Temporary facial-verification result.
                'face_match_score' => 0.9500,

                'face_match_passed' => true,

                // Time when student recorded the activity.
                'activity_timestamp' =>
                    $entry->activity_date . ' ' .
                    $entry->activity_time,

                // Time when supervisor verified it.
                'verification_timestamp' => now(),

                'time_check_passed' => true,

                'verification_status' => 'APPROVED',

                'verification_method' =>
                    'STYLUS_FACE_TIME',

                'rejection_reason' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ]);

        // Since verification passed, mark the clinical
        // entry as verified.
        DB::table('clinical_entries')
            ->where('id', $entry->id)
            ->update([
                'status' => 'VERIFIED',
                'updated_at' => now(),
            ]);

        $this->command->info(
            "Supervisor verification completed successfully. ID: {$verificationId}"
        );

        $this->command->info(
            "Clinical entry {$entry->id} is now VERIFIED."
        );
    }
}