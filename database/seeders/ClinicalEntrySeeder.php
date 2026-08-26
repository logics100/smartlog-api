<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicalEntrySeeder extends Seeder
{
    public function run(): void
    {
        // Get the student's assigned logbook.
        $studentLogbook = DB::table('student_logbooks')->first();

        if (!$studentLogbook) {
            $this->command->error('No student logbook found.');
            return;
        }

        // Get the Wound Suturing item from the logbook.
        $item = DB::table('logbook_items')
            ->where('item_name', 'Wound Suturing')
            ->first();

        if (!$item) {
            $this->command->error('Wound Suturing item not found.');
            return;
        }

        // Get the first requirement for Wound Suturing.
        $requirement = DB::table('logbook_item_requirements')
            ->where('logbook_item_id', $item->id)
            ->orderBy('sequence_number')
            ->first();

        // Create the clinical entry.
        $entryId = DB::table('clinical_entries')->insertGetId([
            'student_logbook_id' => $studentLogbook->id,
            'logbook_item_id' => $item->id,
            'logbook_item_requirement_id' => $requirement?->id,

            'activity_date' => now()->toDateString(),
            'activity_time' => now()->format('H:i:s'),

            'facility_name' => 'Provincial Hospital',
            'clinical_area' => 'Surgical Unit',

            'activity_details' =>
                'Test clinical procedure recorded for SmartLog development.',

            'competency_level' => $requirement?->requirement_code,

            'status' => 'PENDING_VERIFICATION',

            'created_offline' => false,
            'sync_status' => 'SYNCED',

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info(
            "Clinical entry created successfully. ID: {$entryId}"
        );
    }
}