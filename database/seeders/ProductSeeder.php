<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'barcode' => '8991001234501',
                'name' => 'Wardah Lightening Day Cream 30ml',
                'category' => 'Skincare',
                'hpp_price' => 28000,
                'sell_price' => 42000,
                'stock' => 25,
                'exp_date' => '2027-06-15',
            ],
            [
                'barcode' => '8991001234502',
                'name' => 'Emina Bright Stuff Moisturizing Cream',
                'category' => 'Skincare',
                'hpp_price' => 15000,
                'sell_price' => 25000,
                'stock' => 30,
                'exp_date' => '2027-08-20',
            ],
            [
                'barcode' => '8991001234503',
                'name' => 'Maybelline Fit Me Foundation 128',
                'category' => 'Makeup',
                'hpp_price' => 65000,
                'sell_price' => 99000,
                'stock' => 12,
                'exp_date' => '2027-12-01',
            ],
            [
                'barcode' => '8991001234504',
                'name' => 'Implora Urban Lip Cream Matte 01',
                'category' => 'Makeup',
                'hpp_price' => 12000,
                'sell_price' => 22000,
                'stock' => 40,
                'exp_date' => '2027-09-10',
            ],
            [
                'barcode' => '8991001234505',
                'name' => 'Somethinc Niacinamide Serum 20ml',
                'category' => 'Skincare',
                'hpp_price' => 45000,
                'sell_price' => 75000,
                'stock' => 18,
                'exp_date' => '2027-04-25',
            ],
            [
                'barcode' => '8991001234506',
                'name' => 'Pixy UV Whitening BB Cream',
                'category' => 'Makeup',
                'hpp_price' => 18000,
                'sell_price' => 32000,
                'stock' => 20,
                'exp_date' => '2027-07-30',
            ],
            [
                'barcode' => '8991001234507',
                'name' => 'Garnier Micellar Water Pink 125ml',
                'category' => 'Cleanser',
                'hpp_price' => 20000,
                'sell_price' => 35000,
                'stock' => 35,
                'exp_date' => '2028-01-15',
            ],
            [
                'barcode' => '8991001234508',
                'name' => 'Hanasui Collagen Water Sunscreen SPF50',
                'category' => 'Sunscreen',
                'hpp_price' => 14000,
                'sell_price' => 25000,
                'stock' => 22,
                'exp_date' => '2027-11-20',
            ],
            [
                'barcode' => '8991001234509',
                'name' => 'MS Glow Whitening Night Cream',
                'category' => 'Skincare',
                'hpp_price' => 35000,
                'sell_price' => 58000,
                'stock' => 15,
                'exp_date' => '2027-05-18',
            ],
            [
                'barcode' => '8991001234510',
                'name' => 'Scarlett Whitening Body Lotion Romansa',
                'category' => 'Body Care',
                'hpp_price' => 32000,
                'sell_price' => 49000,
                'stock' => 28,
                'exp_date' => '2027-10-05',
            ],
            [
                'barcode' => '8991001234511',
                'name' => 'Y.O.U Rouge Velvet Matte Lip Cream',
                'category' => 'Makeup',
                'hpp_price' => 25000,
                'sell_price' => 45000,
                'stock' => 0,
                'exp_date' => '2027-03-12',
            ],
            [
                'barcode' => '8991001234512',
                'name' => 'Pond\'s Age Miracle Day Cream 50g',
                'category' => 'Skincare',
                'hpp_price' => 70000,
                'sell_price' => 115000,
                'stock' => 8,
                'exp_date' => '2025-01-01',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
