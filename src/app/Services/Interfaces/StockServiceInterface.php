<?php
namespace App\Services\Interfaces;

interface StockServiceInterface
{
    public function updateStock(array $ingredientUpdates): array;
}
