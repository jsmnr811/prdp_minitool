<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB; 

class UserProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        $faker = Faker::create();

        $provinceIds = DB::table('provinces')->pluck('id');

        foreach ($provinceIds as $provinceId) {
            // Create user
            $userId = DB::table('users')->insertGetId([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'contact_number' => $faker->phoneNumber,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Generate 5 unique codes
            $codes = [];
            while (count($codes) < 5) {
                $code = strtoupper(Str::random(8));
                if (!in_array($code, $codes)) {
                    $codes[] = $code;
                }
            }

            // Insert into user_information with codes as JSON string in `code` field
            DB::table('user_information')->insert([
                'user_id' => $userId,
                'province_id' => $provinceId,
                'group' => 1,
                'code' => json_encode($codes),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
