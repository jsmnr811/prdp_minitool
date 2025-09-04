<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CommoditiesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('commodities')->delete();
        
        \DB::table('commodities')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Cassava',
                'abbr' => 'Cassava',
                'icon' => 'commodities/cassava.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Mango',
                'abbr' => 'Mango',
                'icon' => 'commodities/mango.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Sweet Potato',
                'abbr' => 'Sweet Potato',
                'icon' => 'commodities/sweet-potato.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Tilapia',
                'abbr' => 'Tilapia',
                'icon' => 'commodities/tilapia.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Dairy',
                'abbr' => 'Dairy',
                'icon' => 'commodities/dairy.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'Goat',
                'abbr' => 'Goat',
                'icon' => 'commodities/goat.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'Organic garlic',
                'abbr' => 'Organic garlic',
                'icon' => 'commodities/garlic.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'Banana',
                'abbr' => 'Banana',
                'icon' => 'commodities/banana.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'Coffee',
                'abbr' => 'Coffee',
                'icon' => 'commodities/coffee.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            9 => 
            array (
                'id' => 10,
            'name' => 'Citrus (Satsuma, Mandarin)',
                'abbr' => 'Citrus',
                'icon' => 'commodities/citrus.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'Cacao',
                'abbr' => 'Cacao',
                'icon' => 'commodities/cacao.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'Coconut',
                'abbr' => 'Coconut',
                'icon' => 'commodities/coconut.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'Rubber',
                'abbr' => 'Rubber',
                'icon' => 'commodities/rubber.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            13 => 
            array (
                'id' => 14,
                'name' => 'Onion',
                'abbr' => 'Onion',
                'icon' => 'commodities/onion.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            14 => 
            array (
                'id' => 15,
                'name' => 'Tuna',
                'abbr' => 'Tuna',
                'icon' => 'commodities/tuna.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            15 => 
            array (
                'id' => 16,
                'name' => 'Ampalaya',
                'abbr' => 'Ampalaya',
                'icon' => 'commodities/ampalaya.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            16 => 
            array (
                'id' => 17,
                'name' => 'Abaca',
                'abbr' => 'Abaca',
                'icon' => 'commodities/abaca.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            17 => 
            array (
                'id' => 18,
                'name' => 'Oil Palm',
                'abbr' => 'Oil Palm',
                'icon' => 'commodities/oil-palm.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            18 => 
            array (
                'id' => 19,
                'name' => 'Beef Cattle',
                'abbr' => 'Beef Cattle',
                'icon' => 'commodities/beef-cattle.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            19 => 
            array (
                'id' => 20,
                'name' => 'Chicken',
                'abbr' => 'Chicken',
                'icon' => 'commodities/chicken.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            20 => 
            array (
                'id' => 21,
                'name' => 'Seaweeds',
                'abbr' => 'Seaweeds',
                'icon' => 'commodities/seaweed.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            21 => 
            array (
                'id' => 22,
                'name' => 'Pineapple',
                'abbr' => 'Pineapple',
                'icon' => 'commodities/pineapple.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            22 => 
            array (
                'id' => 23,
                'name' => 'Aromatic/Pigmented Rice',
                'abbr' => 'Aromatic',
                'icon' => 'commodities/rice.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            23 => 
            array (
                'id' => 24,
                'name' => 'Mungbean',
                'abbr' => 'Mungbean',
                'icon' => 'commodities/mungbean.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            24 => 
            array (
                'id' => 25,
                'name' => 'Pili',
                'abbr' => 'Pili',
                'icon' => 'commodities/pili.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            25 => 
            array (
                'id' => 26,
                'name' => 'Arrowroot',
                'abbr' => 'Arrowroot',
                'icon' => 'commodities/arrowroot.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            26 => 
            array (
                'id' => 27,
                'name' => 'Calamansi',
                'abbr' => 'Calamansi',
                'icon' => 'commodities/calamansi.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            27 => 
            array (
                'id' => 28,
                'name' => 'Cashew',
                'abbr' => 'Cashew',
                'icon' => 'commodities/cashew.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            28 => 
            array (
                'id' => 29,
                'name' => 'Dairy Milk',
                'abbr' => 'Dairy Milk',
                'icon' => 'commodities/milk.png',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}