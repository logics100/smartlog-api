<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $student = DB::table('users')
            ->where('email', 'student.mbbs@smartlog.local')
            ->value('id');

        $lecturer = DB::table('users')
            ->where('email', 'lecturer.surgery@smartlog.local')
            ->value('id');

        $unit = DB::table('units')
            ->where('unit_code', 'SURG-Y3')
            ->value('id');

        DB::table('enrollments')->insert([
            [
                'student_id' => $student,
                'unit_id' => $unit,
                'enrolled_by' => $lecturer,
                'enrollment_date' => now()->toDateString(),
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}