<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'department_code' => 'MBBS',
                'department_name' => 'Medicine and Surgery',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_code' => 'REHAB',
                'department_name' => 'Rehabilitation Sciences',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_code' => 'RH',
                'department_name' => 'Rural Health',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}