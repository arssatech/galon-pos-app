<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the product seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Aqua Galon 19L',
                'description' => 'Air mineral Aqua dalam galon 19 liter',
                'price' => 24000,
                'stock' => 50,
            ],
            [
                'name' => 'Le Minerale Galon 19L',
                'description' => 'Air mineral Le Minerale dalam galon 19 liter',
                'price' => 23000,
                'stock' => 50,
            ],
            [
                'name' => 'Pristine Galon 19L',
                'description' => 'Air mineral Pristine dalam galon 19 liter',
                'price' => 25000,
                'stock' => 50,
            ],
            [
                'name' => 'Aqua Botol 600ml',
                'description' => 'Botol Aqua 600 ml',
                'price' => 3000,
                'stock' => 200,
            ],
            [
                'name' => 'Le Minerale Botol 1L',
                'description' => 'Botol Le Minerale 1 L',
                'price' => 5000,
                'stock' => 150,
            ],
        ];

        foreach ($products as $data) {
            Product::create($data);
        }
    }
}
