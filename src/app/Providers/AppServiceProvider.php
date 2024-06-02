<?php

namespace App\Providers;

use App\Services\ConfigService;
use App\Services\Interfaces\ConfigServiceInterface;
use App\Services\Interfaces\NotificationServiceInterface;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\Interfaces\StockServiceInterface;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ConfigServiceInterface::class, ConfigService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(StockServiceInterface::class, StockService::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
