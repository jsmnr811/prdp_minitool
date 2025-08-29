<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $startBidDate = Carbon::create(2025, 5, 23, 13, 0, 0);
        $endBidDate = Carbon::create(2025, 5, 30, 17, 0, 0);

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the existing tables
        DB::table('products')->truncate();
        DB::table('auctions')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Products data
        $products = [
            [
                'lot_number' => '1',
                'rank' => '1',
                'variety' => 'Arabica',
                'green_grade' => 'Specialty',
                'origin' => 'Benguet',
                'process' => 'Washed',
                'elevation' => 1275,
                'cup_score' => 87.2,
                'cup_profile' => 'Peach,Apricot,Stonefruit,Honey,Papaya,Berries,Cherries',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '2',
                'rank' => '2',
                'variety' => 'Arabica',
                'green_grade' => 'Specialty',
                'origin' => 'Benguet',
                'process' => 'Washed',
                'elevation' => 1315,
                'cup_score' => 82.95,
                'cup_profile' => 'Caramel,Chocolate,Dates,Prunes,Nuts',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '3',
                'rank' => '3',
                'variety' => 'Arabica',
                'green_grade' => 'Specialty',
                'origin' => 'Sagada',
                'process' => 'Washed',
                'elevation' => 1200,
                'cup_score' => 82.9,
                'cup_profile' => 'Apricot,Cherries,Prunes,Lemon,Caramel,Floral',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '4',
                'rank' => '4',
                'variety' => 'Arabica',
                'green_grade' => 'Specialty',
                'origin' => 'Benguet',
                'process' => 'Washed',
                'elevation' => 1315,
                'cup_score' => 82.5,
                'cup_profile' => 'Pineapple,Caramel,Plum,Vanilla,Chocolate,Mix berries',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '5',
                'rank' => '5',
                'variety' => 'Arabica',
                'green_grade' => 'Specialty',
                'origin' => 'Benguet',
                'process' => 'Washed',
                'elevation' => 1375,
                'cup_score' => 82.4,
                'cup_profile' => 'Prunes,Brown sugar,Nutty,Caramel,Cinnamon',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '6',
                'rank' => '5',
                'variety' => 'Arabica',
                'green_grade' => 'Specialty',
                'origin' => 'Sagada',
                'process' => 'Washed',
                'elevation' => 1450,
                'cup_score' => 82.4,
                'cup_profile' => 'Black Currant,Orange,Berries,Melon,Chocolate',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '7',
                'rank' => null,
                'variety' => 'Arabica',
                'green_grade' => 'Specialty',
                'origin' => 'Benguet',
                'process' => 'Washed',
                'elevation' => 1200,
                'cup_score' => null,
                'cup_profile' => 'Banana,Caramel,Raisins,Green apple,Orange',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '8',
                'rank' => null,
                'variety' => 'Arabica',
                'green_grade' => 'Specialty',
                'origin' => 'Sagada',
                'process' => 'Washed',
                'elevation' => 1400,
                'cup_score' => null,
                'cup_profile' => 'Vanilla,Sugarcane,Dates,Brown sugar',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '9',
                'rank' => '1',
                'variety' => 'Robusta',
                'green_grade' => 'Fine',
                'origin' => 'Kalinga',
                'process' => 'Natural',
                'elevation' => 400,
                'cup_score' => 80.33,
                'cup_profile' => 'Caramel,Raisins,Hazelnut,Honey,Cinnamon',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '10',
                'rank' => '2',
                'variety' => 'Robusta',
                'green_grade' => 'Fine',
                'origin' => 'Kalinga',
                'process' => 'Natural',
                'elevation' => 400,
                'cup_score' => 80.25,
                'cup_profile' => 'Hazelnut,Cinnamon,Tamarind,Chocolate,Anise',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '11',
                'rank' => '2',
                'variety' => 'Robusta',
                'green_grade' => 'Fine',
                'origin' => 'Quirino',
                'process' => 'Natural',
                'elevation' => 200,
                'cup_score' => 80.25,
                'cup_profile' => 'Peanuts,Vanilla,Dark Chocolate,Fruitwine',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'lot_number' => '12',
                'rank' => '4',
                'variety' => 'Robusta',
                'green_grade' => 'Fine',
                'origin' => 'Ifugao',
                'process' => 'Natural',
                'elevation' => 700,
                'cup_score' => 80.00,
                'cup_profile' => 'Caramel,Cinnamon,Vanilla,Almonds',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert products into the database
        DB::table('products')->insert($products);

        // Starting bid prices based on product IDs
        $startingBidPrices = [
            1 => 1500.00,
            2 => 1000.00,
            3 => 900.00,
            4 => 850.00,
            5 => 800.00,
            6 => 800.00,
            7 => 700.00,
            8 => 700.00,
            9 => 650.00,
            10 => 600.00,
            11 => 600.00,
            12 => 550.00
        ];

        // Get the product IDs for creating auctions
        $productIds = DB::table('products')->pluck('id');

        // Prepare auction data
        $auctions = [];
        foreach ($productIds as $productId) {
            // Get the starting bid price for the product
            $startingBidPrice = $startingBidPrices[$productId] ?? 1000.00; // Default to 1000 if not found
            $auctions[] = [
                'product_id' => $productId,
                'starting_bid_price' => $startingBidPrice,
                'lot_size' => 29.5, // Static lot size
                'start_bid_date' => $startBidDate,
                'end_bid_date' => $endBidDate,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert auctions into the database
        DB::table('auctions')->insert($auctions);
    }
}
