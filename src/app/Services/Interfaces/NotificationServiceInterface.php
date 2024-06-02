<?php
namespace App\Services\Interfaces;

interface NotificationServiceInterface
{
    public function sendStockAlert(array $ingredients): void;
}
