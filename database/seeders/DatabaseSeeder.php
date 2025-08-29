<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Testing\Fakes\Fake;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
            FakerUserSeeder::class,
            CommoditiesSeeder::class
        ]);
        $this->call(RegionsTableSeeder::class);
        $this->call(ProvincesTableSeeder::class);
    }
}
