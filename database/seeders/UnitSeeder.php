<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $mbbs = DB::table('departments')
            ->where('department_code', 'MBBS')
            ->value('id');

        $year3 = DB::table('year_levels')
            ->where('year_number', 3)
            ->value('id');

        $semester1 = DB::table('semesters')
            ->where('semester_number', 1)
            ->value('id');

        DB::table('units')->insert([
            [
                'unit_code' => 'SURG-Y3',
                'unit_name' => 'Surgery',
                'department_id' => $mbbs,
                'year_level_id' => $year3,
                'semester_id' => $semester1,
                'requires_logbook' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}