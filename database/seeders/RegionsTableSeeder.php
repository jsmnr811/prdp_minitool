<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RegionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('regions')->delete();

        \DB::table('regions')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'REGION I - ILOCOS REGION',
                'code' => '1',
                'abbr' => 'REGION 1',
                'order' => 3,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-06-30 08:51:22',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'REGION II - CAGAYAN VALLEY',
                'code' => '2',
                'abbr' => 'REGION 2',
                'order' => 4,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-06-30 08:51:22',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'REGION III - CENTRAL LUZON',
                'code' => '3',
                'abbr' => 'REGION 3',
                'order' => 5,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-06-30 08:51:22',
            ),
            3 =>
            array (
                'id' => 5,
                'name' => 'REGION V - BICOL REGION',
                'code' => '5',
                'abbr' => 'REGION 5',
                'order' => 8,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-06-30 08:51:22',
            ),
            4 =>
            array (
                'id' => 6,
                'name' => 'REGION VI - WESTERN VISAYAS',
                'code' => '6',
                'abbr' => 'REGION 6',
                'order' => 9,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-06-30 08:51:22',
            ),
            5 =>
            array (
                'id' => 7,
                'name' => 'REGION VII - CENTRAL VISAYAS',
                'code' => '7',
                'abbr' => 'REGION 7',
                'order' => 11,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-07-02 09:36:16',
            ),
            6 =>
            array (
                'id' => 8,
                'name' => 'REGION VIII - EASTERN VISAYAS',
                'code' => '8',
                'abbr' => 'REGION 8',
                'order' => 12,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-07-02 09:36:16',
            ),
            7 =>
            array (
                'id' => 9,
                'name' => 'REGION IX - ZAMBOANGA PENINSULA',
                'code' => '9',
                'abbr' => 'REGION 9',
                'order' => 13,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-07-02 09:36:16',
            ),
            8 =>
            array (
                'id' => 10,
                'name' => 'REGION X - NORTHERN MINDANAO',
                'code' => '10',
                'abbr' => 'REGION 10',
                'order' => 14,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-07-02 09:36:16',
            ),
            9 =>
            array (
                'id' => 11,
                'name' => 'REGION XI - DAVAO REGION',
                'code' => '11',
                'abbr' => 'REGION 11',
                'order' => 15,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-07-02 09:36:16',
            ),
            10 =>
            array (
                'id' => 12,
                'name' => 'REGION XII - SOCCSSARGEN',
                'code' => '12',
                'abbr' => 'REGION 12',
                'order' => 16,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-07-02 09:36:16',
            ),
            11 =>
            array (
                'id' => 13,
                'name' => 'REGION XIII - CARAGA',
                'code' => '13',
                'abbr' => 'CARAGA',
                'order' => 17,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-07-02 09:36:16',
            ),
            12 =>
            array (
                'id' => 14,
                'name' => 'BANGSAMORO AUTONOMOUS REGION IN MUSLIM MINDANAO',
                'code' => '14',
                'abbr' => 'BARMM',
                'order' => 18,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-07-02 09:36:16',
            ),
            13 =>
            array (
                'id' => 15,
                'name' => 'CORDILLERA ADMINISTRATIVE REGION',
                'code' => '15',
                'abbr' => 'CAR',
                'order' => 2,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-06-30 08:51:22',
            ),
            14 =>
            array (
                'id' => 16,
                'name' => 'NATIONAL CAPITAL REGION',
                'code' => '16',
                'abbr' => 'NCR',
                'order' => 1,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-06-30 08:51:22',
            ),
            15 =>
            array (
                'id' => 17,
                'name' => 'REGION IV-A - CALABARZON',
                'code' => '40',
                'abbr' => 'REGION 4A',
                'order' => 6,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-06-30 08:51:22',
            ),
            16 =>
            array (
                'id' => 18,
                'name' => 'REGION IV-B- MIMAROPA',
                'code' => '41',
                'abbr' => 'REGION 4B',
                'order' => 7,
                'status' => 1,
                'created_at' => '2025-06-30 08:51:22',
                'updated_at' => '2025-06-30 08:51:22',
            ),
            17 =>
            array (
                'id' => 19,
                'name' => 'NEGROS ISLAND REGION',
                'code' => '42',
                'abbr' => 'NIR',
                'order' => 10,
                'status' => 1,
                'created_at' => '2025-07-02 09:36:03',
                'updated_at' => '2025-07-02 09:36:16',
            ),
        ));


    }
}
