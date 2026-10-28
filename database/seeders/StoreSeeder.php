<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Customer;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [

            ['Colgate Toothpaste', 'PROD-0001', 50,  8,  40],
            ['Parle-G Biscuit',    'PROD-0002', 10,  8,  120],
            ['Bread',              'PROD-0003', 40,  5,  4],
            ['Milk 1L',            'PROD-0004', 60,  5,  9],
            ['Eggs (12)',          'PROD-0005', 84,  5,  2],
            ['Basmati Rice 1kg',   'PROD-0006', 110, 5,  35],
            ['Tea Powder 250g',    'PROD-0007', 130, 12, 25],
            ['Sunflower Oil 1L',   'PROD-0008', 150, 12, 18],
            ['Soap Bar',           'PROD-0009', 35,  18, 60],
            ['Notebook',           'PROD-0010', 45,  12, 50],
        ];

        foreach ($products as [$name, $code, $price, $tax, $stock]) {
            Product::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'price' => $price,
                    'tax_percentage' => $tax,
                    'stock_on_hand' => $stock,
                ]
            );
        }

        Customer::updateOrCreate(
            ['email' => 'thomas@example.com'],
            ['name' => 'Thomas']
        );

        Customer::factory()->count(4)->create();
    }
}
