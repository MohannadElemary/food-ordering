<?php
namespace App\Services\Interfaces;

use App\Exceptions\InsufficientIngredientsException;

interface OrderServiceInterface
{
    /**
     * @throws InsufficientIngredientsException
     */
    public function createOrder(array $products): void;
}
