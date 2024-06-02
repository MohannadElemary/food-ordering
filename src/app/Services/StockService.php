<?php
namespace App\Services;

use App\Models\Ingredient;
use App\Services\Interfaces\StockServiceInterface;

class StockService implements StockServiceInterface
{
    public function updateStock(array $ingredientUpdates): array
    {
        $lowStockIngredients = [];

        foreach ($ingredientUpdates as $update) {
            $ingredient = $update['ingredient'];
            $ingredient->quantity -= $update['totalRequired'];
            $ingredient->save();

            if ($ingredient->quantity <= ($ingredient->initial_stock * 0.5) && !$ingredient->alert_sent) {
                $lowStockIngredients[] = $ingredient;
            }
        }

        if (!empty($lowStockIngredients)) {
            $this->updateAlertSentStatus($lowStockIngredients);
        }

        return $lowStockIngredients;
    }

    protected function updateAlertSentStatus($ingredients): void
    {
        $ingredientIds = array_map(function ($ingredient) {
            return $ingredient->id;
        }, $ingredients);

        Ingredient::whereIn('id', $ingredientIds)->update(['alert_sent' => true]);
    }
}
