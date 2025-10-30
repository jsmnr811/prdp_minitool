<?php

namespace Database\Seeders\HRMS;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('mysql2')->statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::connection('mysql2')->table('offices')->truncate();
        DB::connection('mysql2')->statement('SET FOREIGN_KEY_CHECKS=1;');

        $offices = [
            ['code' => 'OPD', 'name' => 'Office of Project Director'],
            ['code' => 'ONPD', 'name' => 'Office of the National Deputy Project Director'],
        ];

        foreach ($offices as $office) {
            DB::connection('mysql2')->table('offices')->insert([
                'code' => $office['code'],
                'name' => $office['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
