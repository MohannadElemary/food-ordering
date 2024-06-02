<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductIngredientsTableSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['product_id' => 1, 'ingredient_id' => 1, 'amount' => 150],
            ['product_id' => 1, 'ingredient_id' => 2, 'amount' => 30],
            ['product_id' => 1, 'ingredient_id' => 3, 'amount' => 20],
        ];

        foreach ($data as $item) {
            DB::table('product_ingredients')->updateOrInsert(
                ['product_id' => $item['product_id'], 'ingredient_id' => $item['ingredient_id']],
                ['amount' => $item['amount']]
            );
        }
    }
}
