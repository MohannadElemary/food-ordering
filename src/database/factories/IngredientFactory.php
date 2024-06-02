<?php
namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'quantity' => $this->faker->numberBetween(1000, 20000),
            'initial_stock' => $this->faker->numberBetween(1000, 20000),
            'alert_sent' => false,
        ];
    }
}
