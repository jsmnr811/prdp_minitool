<?php

namespace Database\Seeders\HRMS;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            'Project Director',
            'Deputy Project Director',
            'Component Head',
            'Alternate Component Head',
            'Unit Head',
            'Alternate Unit Head',
            'Legal Officer',
            'Senior Planning Specialist',
            'Senior Rural Infrastructure Specialist',
            'Senior Project Development Specialist',
            'Senior Institutional Development Specialist',
            'Senior Economist',
            'Project Accountant',
            'Rural Infrastructure Specialist',
            'Planning Specialist',
            'Business Development Specialist',
            'Enterprise Development & Marketing Specialist',
            'Organizational Development Specialist',
            'Monitoring and Evaluation Specialist',
            'Management Information System Specialist (Output-Based)',
            'Knowledge Management Specialist',
            'Social Safeguards Specialist',
            'Environmental Safeguards Specialist',
            'Procurement Specialist',
            'GIS Data Specialist',
            'Information Specialist',
            'Institutional Development Specialist',
            'Finance Management Specialist',
            'Financial Specialist',
            'Financial Analyst III',
            'Budget Specialist',
            'Cashier',
            'Compliance Officer',
            'Economist',
            'Rural Infrastructure Engineer',
            'Planning Officer',
            'Project Development Officer',
            'MIS Officer',
            'Monitoring and Evaluation Officer',
            'Knowledge Management Officer',
            'GIS Data Officer',
            'Procurement Officer',
            'Social and Environmental Safeguards Officer',
            'GRM Officer',
            'Human Resource Management Officer',
            'Information Officer',
            'Institutional Development Officer',
            'Budget Officer',
            'Media Production Officer',
            'Financial Management Associate',
            'Financial Analyst I',
            'Budget Analyst',
            'Rural Infrastructure Associate',
            'Enterprise Development Associate',
            'Associate Economist',
            'Media Production Associate',
            'Associate Procurement Officer',
            'Associate M & E Officer',
            'Associate SES Officer',
            'Financial Management Assistant',
            'Legal Assistant',
            'Administrative Officer III',
            'Supply and Property Officer',
            'Project Development Associate',
            'Photographer/Videographer',
            'Administrative Officer II',
            'Administrative Officer I',
            'Associate Supply & Property Officer',
            'Administrative Assistant',
            'HRMA',
            'Cash Clerk',
            'Driver/Mechanic',
            'Driver',
            'Administrative Aide',
        ];

        // ✅ Disable FK checks to safely truncate
        DB::connection('mysql2')->statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::connection('mysql2')->table('positions')->truncate();
        DB::connection('mysql2')->statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insert positions
        foreach ($positions as $position) {
            DB::connection('mysql2')->table('positions')->insert([
                'name' => $position,
                'component_id' => null, 
                'unit_id' => null,     
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
