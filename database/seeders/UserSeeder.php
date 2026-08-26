<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $mbbs = DB::table('departments')
            ->where('department_code', 'MBBS')
            ->value('id');

        $year3 = DB::table('year_levels')
            ->where('year_number', 3)
            ->value('id');

        DB::table('users')->insert([
            [
                'name' => 'SmartLog ICT Admin',
                'email' => 'admin@smartlog.local',
                'password' => Hash::make('password123'),
                'dwu_id' => 'ADMIN001',
                'department_id' => null,
                'year_level_id' => null,
                'role' => 'ICT_ADMIN',
                'phone' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Test MBBS HOD',
                'email' => 'hod.mbbs@smartlog.local',
                'password' => Hash::make('password123'),
                'dwu_id' => 'HOD001',
                'department_id' => $mbbs,
                'year_level_id' => null,
                'role' => 'HOD',
                'phone' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Test Surgery Lecturer',
                'email' => 'lecturer.surgery@smartlog.local',
                'password' => Hash::make('password123'),
                'dwu_id' => 'LEC001',
                'department_id' => $mbbs,
                'year_level_id' => null,
                'role' => 'LECTURER',
                'phone' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Test MBBS Student',
                'email' => 'student.mbbs@smartlog.local',
                'password' => Hash::make('password123'),
                'dwu_id' => 'STU001',
                'department_id' => $mbbs,
                'year_level_id' => $year3,
                'role' => 'STUDENT',
                'phone' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}