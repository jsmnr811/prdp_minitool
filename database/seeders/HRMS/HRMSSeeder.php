<?php

namespace Database\Seeders\HRMS;

use Illuminate\Database\Seeder;

class HRMSSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ComponentSeeder::class,
            PositionSeeder::class,
            OfficeSeeder::class
        ]);
    }
}
