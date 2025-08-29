<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InterventionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $interventions = [
            "Common Service Facility",
            "Capacity Building",
            "Farm to Market Road (FMR)",
            "Standards",
            "Post-harvest Facility",
            "Trading Post",
            "Market Link",
            "Processing Facility",
            "Farm Equipment",
            "Nursery",
            "Production Facility",
            "Breeding Facility",
            "Research Facility",
            "Transport Facility",
            "Marketing Facility",
            "Research and Development",
            "Animal Dispersal",
            "Planting Materials",
            "Credit",
            "Research Study",
            "Cold Storage Facility",
            "Fertilizer Facility",
            "Irrigation (CIS)",
            "Potable Water Supply (PWS)",
            "Inputs",
            "Warehouse and Storage",
            "Capacity Building & Support Service",
            "Credit & Cash Payments",
            "Cooperative & A&F Groups",
            "Certification & Accreditation",
            "Fish Landing",
            "Production Area",
            "Production Facility & Nursery",
            "Bridge",
            "Policy Development",
            "Marketing Activity",
            "Planting Material Dispersal",
            "Environmental Conservation",
            "Compliance Facility",
            "Market Linkage",
            "Technology Development",
            "Input Dispersal",
            "Enterprise",
            "Mapping",
            "Integrated Pest Management (IPM)",
            "Farm to Market Road with Bridge",
            "Irrigation (SSIP)",
            "Warehouse & Storage",
            "Abattoir & Slaughterhouse",
            "Packaging",
            "Incentive & Recognition",
            "Livelihood Project",
            "Regulatory Service",
            "Tram Line",
            "Fish Sanctuary & MPA",
            "Port",
            "Solar Dryer",
            "Veterinary Service",
            "Capacity Building & Support Services",
            "Incentive and Recognition",
        ];

        foreach ($interventions as $intervention) {
            DB::table('interventions')->insert([
                'name' => $intervention,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
