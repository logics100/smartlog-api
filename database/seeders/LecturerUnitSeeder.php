<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LecturerUnitSeeder extends Seeder
{
    public function run(): void
    {
        $lecturer = DB::table('users')
            ->where('email', 'lecturer.surgery@smartlog.local')
            ->value('id');

        $unit = DB::table('units')
            ->where('unit_code', 'SURG-Y3')
            ->value('id');

        DB::table('lecturer_units')->insert([
            [
                'lecturer_id' => $lecturer,
                'unit_id' => $unit,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}