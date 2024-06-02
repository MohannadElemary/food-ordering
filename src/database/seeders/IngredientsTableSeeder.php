<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ingredient;

class IngredientsTableSeeder extends Seeder
{
    public function run(): void
    {
        $ingredients = [
            ['name' => 'Beef', 'quantity' => 20000],
            ['name' => 'Cheese', 'quantity' => 5000],
            ['name' => 'Onion', 'quantity' => 1000]
        ];

        foreach ($ingredients as $data) {
            $ingredient = Ingredient::where('name', $data['name'])->first();

            if ($ingredient) {
                $newQuantity = $ingredient->quantity + $data['quantity'];
                $ingredient->update([
                    'quantity' => $newQuantity,
                    'initial_stock' => $newQuantity,
                    'alert_sent' => false
                ]);
            } else {
                Ingredient::create([
                    'name' => $data['name'],
                    'quantity' => $data['quantity'],
                    'initial_stock' => $data['quantity'],
                    'alert_sent' => false
                ]);
            }
        }
    }
}
