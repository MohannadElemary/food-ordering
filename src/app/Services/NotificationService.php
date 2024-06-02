<?php
namespace App\Services;

use App\Services\Interfaces\ConfigServiceInterface;
use App\Services\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Mail;
use App\Mail\StockAlertMail;

class NotificationService implements NotificationServiceInterface
{
    protected ConfigServiceInterface $configService;

    public function __construct(ConfigServiceInterface $configService)
    {
        $this->configService = $configService;
    }

    public function sendStockAlert(array $ingredients): void
    {
        $merchantEmail = $this->configService->get('merchant_email');
        Mail::to($merchantEmail)->queue(new StockAlertMail($ingredients));
    }
}
