<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\HRMS\HRMSSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call([
        //     ProductSeeder::class,
        //     FakerUserSeeder::class,
        //     CommoditiesTableSeeder::class
        // ]);
        // $this->call(RegionsTableSeeder::class);
        // $this->call(ProvincesTableSeeder::class);
        // $this->call(ProvincesBoundariesSeeder::class);
        // $this->call(InterventionsTableSeeder::class);
        // $this->call(GeoOfficeSeeder::class);
        // $this->call(CommoditiesTableSeeder::class);

        $this->call(HRMSSeeder::class);

    }
}
