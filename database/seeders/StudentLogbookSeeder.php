<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentLogbookSeeder extends Seeder
{
    public function run(): void
    {
        $student = DB::table('users')
            ->where('email', 'student.mbbs@smartlog.local')
            ->first();

        if (!$student) {
            $this->command->error('Test MBBS student not found.');
            return;
        }

        $unit = DB::table('units')
            ->where('unit_code', 'SURG-Y3')
            ->first();

        if (!$unit) {
            $this->command->error('Surgery unit not found.');
            return;
        }

        $enrollment = DB::table('enrollments')
            ->where('student_id', $student->id)
            ->where('unit_id', $unit->id)
            ->first();

        if (!$enrollment) {
            $this->command->error('Student enrollment not found.');
            return;
        }

        $template = DB::table('logbook_templates')
            ->where('unit_id', $unit->id)
            ->first();

        if (!$template) {
            $this->command->error('Logbook template not found.');
            return;
        }

        DB::table('student_logbooks')->insert([
            'enrollment_id' => $enrollment->id,
            'logbook_template_id' => $template->id,
            'assigned_date' => now()->toDateString(),
            'due_date' => null,
            'status' => 'ACTIVE',
            'completion_percentage' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info(
            'MBBS Year 3 Surgery logbook assigned to test student.'
        );
    }
}