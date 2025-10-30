<?php

namespace Database\Seeders\HRMS;

use Illuminate\Database\Seeder;
use App\Models\HRMS\Component;
use App\Models\HRMS\Unit;

class ComponentSeeder extends Seeder
{
    public function run(): void
    {
        // Core Components
        $components = [
            ['code' => 'I-BUILD', 'name' => 'Intensified Building Up of Infrastructure and Logistics for Development'],
            ['code' => 'I-REAP', 'name' => 'Investments for Rural Enterprises and Agricultural and Fisheries Productivity'],
            ['code' => 'I-PLAN', 'name' => 'Investments for AFMP Planning at the Local and National Levels'],
            ['code' => 'I-SUPPORT', 'name' => 'Implementation Support to PRDP'],
        ];

        foreach ($components as $compData) {
            $component = Component::create($compData);

            // Attach units only for I-SUPPORT
            if ($component->code === 'I-SUPPORT') {
                $units = [
                    ['code' => 'ACCOUNTING', 'name' => 'Accounting'],
                    ['code' => 'FINANCE', 'name' => 'Finance'],
                    ['code' => 'PROCUREMENT', 'name' => 'Procurement'],
                    ['code' => 'BUDGET', 'name' => 'Budget'],
                    ['code' => 'ADMIN', 'name' => 'Administrative Unit'],
                    ['code' => 'M&E', 'name' => 'Monitoring and Evaluation'],
                    ['code' => 'SES', 'name' => 'Social and Environmental Safeguards'],
                    ['code' => 'GGU', 'name' => 'Geomapping and Governance Unit'],
                    ['code' => 'INFOACE', 'name' => 'Information, Advocacy, Communication and Education'],
                    ['code' => 'IDU', 'name' => 'Institutional Development Unit'],
                ];

                foreach ($units as $unit) {
                    Unit::create(array_merge($unit, ['component_id' => $component->id]));
                }
            }
        }
    }
}
