<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicalSupervisorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('clinical_supervisors')->insert([
            'full_name' => 'Test Clinical Supervisor',
            'facility_name' => 'Provincial Hospital',
            'profession' => 'Doctor',
            'position_title' => 'Clinical Supervisor',
            'registration_number' => 'TEST-SUP-001',

            // We are NOT implementing real facial recognition yet.
            'reference_face_path' => null,

            'is_verified_supervisor' => true,
            'is_active' => true,

            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}