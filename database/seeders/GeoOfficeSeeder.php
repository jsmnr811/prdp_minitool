<?php

namespace Database\Seeders;

use App\Models\GeoOffice;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GeoOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $data = [
            ['institution' => 'DA Central Office', 'office' => 'Office of the Secretary'],
            ['institution' => 'DA Central Office', 'office' => 'Office of the Undersecretary for Policy, Planning, and Regulations'],
            ['institution' => 'DA Central Office', 'office' => 'Office of the Undersecretary for Operations'],
            ['institution' => 'DA Central Office', 'office' => 'Office of the Undersecretary for Rice'],
            ['institution' => 'DA Central Office', 'office' => 'Office of the Undersecretary for Finance'],
            ['institution' => 'DA Central Office', 'office' => 'Office of the Assistant Secretary for Planning and Project Development'],
            ['institution' => 'DA Central Office', 'office' => 'Office of the Assistant Secretary for Policy and Regulations'],
            ['institution' => 'DA Central Office', 'office' => 'Field Operations Service (FOS)'],
            ['institution' => 'DA Central Office', 'office' => 'Planning and Monitoring Service (PMS)'],
            ['institution' => 'DA Central Office', 'office' => 'Project Development Service (PDS)'],
            ['institution' => 'DA Central Office', 'office' => 'Policy Research Service (PRS)'],
            ['institution' => 'DA Central Office', 'office' => 'Agribusiness and Marketing Assistance Service (AMAS)'],
            ['institution' => 'DA Central Office', 'office' => 'DA Information and Communications Technology Service (ICTS)'],
            ['institution' => 'DA Central Office', 'office' => 'DA Agriculture and Fisheries Information Division (AFID)'],

            ['institution' => 'DA Bureau', 'office' => 'Agricultural Training Institute (ATI)'],
            ['institution' => 'DA Bureau', 'office' => 'Bureau of Fisheries and Aquatic Resources (BFAR)'],
            ['institution' => 'DA Bureau', 'office' => 'Bureau of Agricultural and Fisheries Engineering (BAFE)'],
            ['institution' => 'DA Bureau', 'office' => 'BSWM'],

            ['institution' => 'DA Attached Agency/Corp', 'office' => 'Philippine Coconut Authority (PCA)'],
            ['institution' => 'DA Attached Agency/Corp', 'office' => 'PhilMech'],
            ['institution' => 'DA Attached Agency/Corp', 'office' => 'PhilRice'],
            ['institution' => 'DA Attached Agency/Corp', 'office' => 'Philippine Council for Agriculture and Fisheries (PCAF)'],
            ['institution' => 'DA Attached Agency/Corp', 'office' => 'Regional Agricultural and Fishery Council (RAFC)'],
            ['institution' => 'DA Attached Agency/Corp', 'office' => 'Philippine Crop Insurance Corporation (PCIC)'],
            ['institution' => 'DA Attached Agency/Corp', 'office' => 'National Irrigation Administration (NIA)'],

            ['institution' => 'DA Banner Program', 'office' => 'National Rice Program'],
            ['institution' => 'DA Banner Program', 'office' => 'National Corn Program'],
            ['institution' => 'DA Banner Program', 'office' => 'High Value Crops Development Program'],
            ['institution' => 'DA Banner Program', 'office' => 'National Livestock Program'],

            ['institution' => 'World Bank Funded Project', 'office' => 'MIADP'],
            ['institution' => 'World Bank Funded Project', 'office' => 'FishCoRe'],

            ['institution' => 'DA Regional Field Office', 'office' => 'Office of the RED'],
            ['institution' => 'DA Regional Field Office', 'office' => 'PMED Chief'],
            ['institution' => 'DA Regional Field Office', 'office' => 'Banner Programs'],

            ['institution' => 'DA PRDP', 'office' => 'NPCO'],
            ['institution' => 'DA PRDP', 'office' => 'PSO'],
            ['institution' => 'DA PRDP', 'office' => 'RPCO'],

            ['institution' => 'BARMM', 'office' => 'MAFAR'],

            ['institution' => 'Provincial Local Government Units', 'office' => 'Governor'],
            ['institution' => 'Provincial Local Government Units', 'office' => 'SP Committee on Agriculture'],
            ['institution' => 'Provincial Local Government Units', 'office' => 'PPDO'],
            ['institution' => 'Provincial Local Government Units', 'office' => 'Provincial Agriculturist'],
            ['institution' => 'Provincial Local Government Units', 'office' => 'Provincial Veterinarian'],
            ['institution' => 'Provincial Local Government Units', 'office' => 'PPMIU Head'],

            ['institution' => 'Government Financial Institutions', 'office' => 'Landbank'],
            ['institution' => 'Government Financial Institutions', 'office' => 'DBP'],

            ['institution' => 'Other Institutions', 'office' => 'World Bank'],
            ['institution' => 'Other Institutions', 'office' => 'Commodity Experts (Resource Persons)'],
            ['institution' => 'Other Institutions', 'office' => 'Tanggol Kalikasan (Facilitators)'],
        ];

        foreach ($data as $item) {
            GeoOffice::create($item);
        }
    }
}
