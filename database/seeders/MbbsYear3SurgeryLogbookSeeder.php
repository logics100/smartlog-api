<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MbbsYear3SurgeryLogbookSeeder extends Seeder
{
    public function run(): void
    {
        $unitId = DB::table('units')
            ->where('unit_code', 'SURG-Y3')
            ->value('id');

        if (!$unitId) {
            $this->command->error('SURG-Y3 unit not found.');
            return;
        }

        $templateId = DB::table('logbook_templates')->insertGetId([
            'unit_id' => $unitId,
            'template_name' => 'MBBS Year 3 Surgery Clinical Skills Logbook',
            'description' => 'Digital version of the Year 3 Surgery clinical skills logbook.',
            'minimum_completion_percentage' => 90,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sections
        $attendanceId = DB::table('logbook_sections')->insertGetId([
            'logbook_template_id' => $templateId,
            'section_title' => 'Record of Attendance',
            'section_type' => 'ATTENDANCE',
            'instructions' => 'Record clinical placement hours for the Surgery unit.',
            'display_order' => 1,
            'requires_supervisor_verification' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $generalId = DB::table('logbook_sections')->insertGetId([
            'logbook_template_id' => $templateId,
            'section_title' => 'General Procedures',
            'section_type' => 'PROCEDURE',
            'instructions' => '* = observe/assist/simulation, ** = perform, *** = formal competency assessment.',
            'display_order' => 2,
            'requires_supervisor_verification' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $imagingId = DB::table('logbook_sections')->insertGetId([
            'logbook_template_id' => $templateId,
            'section_title' => 'Interpretation of Imaging Studies',
            'section_type' => 'PROCEDURE',
            'instructions' => 'Record interpretation of radiological and ultrasound studies.',
            'display_order' => 3,
            'requires_supervisor_verification' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $surgicalId = DB::table('logbook_sections')->insertGetId([
            'logbook_template_id' => $templateId,
            'section_title' => 'Surgical Procedures',
            'section_type' => 'PROCEDURE',
            'instructions' => '* = observe/assist, ** = perform, *** = formal competency assessment.',
            'display_order' => 4,
            'requires_supervisor_verification' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherId = DB::table('logbook_sections')->insertGetId([
            'logbook_template_id' => $templateId,
            'section_title' => 'Other Procedures',
            'section_type' => 'PROCEDURE',
            'instructions' => 'Additional clinical procedures completed during placement.',
            'display_order' => 5,
            'requires_supervisor_verification' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | GENERAL PROCEDURES
        |--------------------------------------------------------------------------
        */

        $this->addItem(
            $generalId,
            'Intramuscular Injection (IM)',
            ['**', '**'],
            1
        );

        $this->addItem(
            $generalId,
            'Intravenous Injection (IV)',
            ['**'],
            2
        );

        $this->addItem(
            $generalId,
            'Taking Blood Sample for Laboratory Tests',
            ['**', '**'],
            3
        );

        $this->addItem(
            $generalId,
            'Connecting IV Infusion',
            ['**', '**'],
            4
        );

        $this->addItem(
            $generalId,
            'Postoperative Chest Physiotherapy',
            ['*', '**'],
            5
        );

        $this->addItem(
            $generalId,
            'Mobilising Patients Post Operation',
            ['*', '**'],
            6
        );

        $this->addItem(
            $generalId,
            'Insertion of NGT',
            ['*', '**'],
            7
        );

        $this->addItem(
            $generalId,
            'Insertion of IDC - Male Patient',
            ['*', '**'],
            8
        );

        $this->addItem(
            $generalId,
            'Insertion of IDC - Female Patient',
            ['*', '**'],
            9
        );

        $this->addItem(
            $generalId,
            'Log Roll for Paralysed Patients',
            ['*', '**'],
            10
        );

        /*
        |--------------------------------------------------------------------------
        | IMAGING
        |--------------------------------------------------------------------------
        */

        $imagingItems = [
            'Bowel Obstruction - X-ray',
            'Perforated Viscus - X-ray',
            'Liver/Spleen Injury - Ultrasound',
            'Gallbladder Stone / Cholecystitis - Ultrasound',
            'Pleural Effusion / Hemothorax - X-ray',
            'Pneumothorax - X-ray',
            'Stone in Urinary Tract - X-ray/Ultrasound',
            'Rib Fracture - X-ray',
            'Supracondylar Fracture - X-ray',
            'Femoral Fracture - X-ray',
            'Spinal Fracture - X-ray',
            'Skull Fracture - X-ray',
            'Ultrasound Examination - Abdomen, Heart or Breast',
        ];

        foreach ($imagingItems as $index => $name) {
            $this->addItem(
                $imagingId,
                $name,
                ['*'],
                $index + 1
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SURGICAL PROCEDURES
        |--------------------------------------------------------------------------
        */

        $this->addItem(
            $surgicalId,
            'Patient Admission and Clerking',
            ['*', '**', '**'],
            1
        );

        $this->addItem(
            $surgicalId,
            'Patient Discharge',
            ['*', '*', '**'],
            2
        );

        $this->addItem(
            $surgicalId,
            'Per Rectum Examination',
            ['*', '*', '**'],
            3
        );

        $this->addItem(
            $surgicalId,
            'Wound Dressing',
            ['*', '*', '*'],
            4
        );

        $this->addItem(
            $surgicalId,
            'Wound Suturing',
            ['*', '*', '**'],
            5
        );

        $this->addItem(
            $surgicalId,
            'Suprapubic Puncture',
            ['*', '*', '**'],
            6,
            true
        );

        $this->addItem(
            $surgicalId,
            'Joint Aspiration / Injection',
            ['*', '*', '*'],
            7
        );

        $this->addItem(
            $surgicalId,
            'Breast Examination',
            ['*', '**', '***'],
            8
        );

        $this->addItem(
            $surgicalId,
            'Abdominal Examination',
            ['*', '**', '**'],
            9
        );

        $this->addItem(
            $surgicalId,
            'Head Injury Patient Examination',
            ['*', '*', '***'],
            10
        );

        $this->addItem(
            $surgicalId,
            'Neurovascular Status of Limbs Examination',
            ['*', '**', '***'],
            11
        );

        $this->addItem(
            $surgicalId,
            'Musculoskeletal System Examination',
            ['*', '**', '**'],
            12
        );

        $this->addItem(
            $surgicalId,
            'Spinal Injury Patient Examination',
            ['*', '**', '***'],
            13
        );

        $this->addItem(
            $surgicalId,
            'Head and Neck Examination',
            ['*', '*', '**'],
            14
        );

        $this->addItem(
            $surgicalId,
            'Observation / Assistance During Surgical Operations',
            ['*', '*', '*'],
            15
        );

        $this->addItem(
            $surgicalId,
            'Incision and Drainage',
            ['*', '*', '*'],
            16
        );

        $this->addItem(
            $surgicalId,
            'Wound Debridement',
            ['*', '*', '*'],
            17
        );

        $this->addItem(
            $surgicalId,
            'Biopsy / Lump Excision / Skin Graft',
            ['*', '*'],
            18
        );

        $this->addItem(
            $surgicalId,
            'Application of Cervical Collar / Orthotic Device',
            ['*', '*', '**'],
            19,
            true
        );

        $this->addItem(
            $surgicalId,
            'Application of POP - Upper Limb',
            ['*', '*', '*'],
            20
        );

        $this->addItem(
            $surgicalId,
            'Application of POP - Lower Limb',
            ['*', '*', '*'],
            21
        );

        $this->addItem(
            $surgicalId,
            'Reduction of Fracture',
            ['*', '*', '*'],
            22
        );

        $this->addItem(
            $surgicalId,
            'Application of Skin / Skeletal Traction',
            ['*', '*', '*'],
            23
        );

        $this->addItem(
            $surgicalId,
            'Removal of POP',
            ['*', '*', '*'],
            24
        );

        $this->addItem(
            $surgicalId,
            'History Taking - Trauma',
            ['*', '**', '**'],
            25
        );

        $this->addItem(
            $surgicalId,
            'History Taking - GIT',
            ['*', '**', '***'],
            26
        );

        $this->addItem(
            $surgicalId,
            'History Taking - Other',
            ['*', '**', '**'],
            27
        );

        $this->command->info(
            'MBBS Year 3 Surgery SmartLog template created.'
        );
    }

    private function addItem(
        int $sectionId,
        string $name,
        array $requirements,
        int $order,
        bool $simulation = false
    ): void {
        $itemId = DB::table('logbook_items')->insertGetId([
            'logbook_section_id' => $sectionId,
            'item_name' => $name,
            'item_description' => null,
            'required_level' => null,
            'required_count' => count($requirements),
            'requires_supervisor_verification' => true,
            'display_order' => $order,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($requirements as $index => $requirement) {

            $label = match ($requirement) {
                '*'   => 'Observe / Assist',
                '**'  => 'Perform',
                '***' => 'Formal Competency Assessment',
                default => $requirement,
            };

            DB::table('logbook_item_requirements')->insert([
                'logbook_item_id' => $itemId,
                'sequence_number' => $index + 1,
                'requirement_code' => $requirement,
                'requirement_label' => $label,
                'is_simulation' => $simulation,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}