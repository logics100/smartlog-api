<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('semesters')->insert([
            [
                'semester_name' => 'Semester 1',
                'semester_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'semester_name' => 'Semester 2',
                'semester_number' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}