<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class YearLevelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('year_levels')->insert([
            [
                'year_name' => 'Year 2',
                'year_number' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year_name' => 'Year 3',
                'year_number' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year_name' => 'Year 4',
                'year_number' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year_name' => 'Year 5',
                'year_number' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}