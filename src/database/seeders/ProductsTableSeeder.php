<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        Product::updateOrCreate(
            ['name' => 'Burger'],
            [
                'description' => 'Delicious beef burger',
                'price' => 5.99
            ]
        );
    }
}
