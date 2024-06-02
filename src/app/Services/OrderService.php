<?php
namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Exceptions\InsufficientIngredientsException;
use App\Services\Interfaces\ConfigServiceInterface;
use App\Services\Interfaces\NotificationServiceInterface;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\Interfaces\StockServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceInterface
{
    protected ConfigServiceInterface $configService;
    protected StockServiceInterface $stockService;
    protected NotificationServiceInterface $notificationService;

    public function __construct(
        ConfigServiceInterface $configService,
        StockServiceInterface $stockService,
        NotificationServiceInterface $notificationService
    ){
        $this->configService = $configService;
        $this->stockService = $stockService;
        $this->notificationService = $notificationService;
    }

    public function createOrder(array $products): void
    {
        DB::transaction(function () use ($products) {
            $productIds = array_column($products, 'product_id');
            $productsWithIngredients = $this->loadProductsWithIngredients($productIds);

            $ingredientUpdates = $this->checkStockAvailability($products, $productsWithIngredients);

            $order = Order::create();
            $this->attachProductsToOrder($order, $productsWithIngredients, $products);
            $lowStockIngredients = $this->stockService->updateStock($ingredientUpdates);

            if (!empty($lowStockIngredients)) {
                $this->notificationService->sendStockAlert($lowStockIngredients);
            }
        });
    }

    protected function loadProductsWithIngredients(array $productIds): Collection
    {
        return Product::with(['ingredients' => function ($query) {
            $query->lockForUpdate();
        }])->findMany($productIds);
    }

    protected function checkStockAvailability(array $products, $productsWithIngredients): array
    {
        $ingredientUpdates = [];

        foreach ($products as $productData) {
            $product = $productsWithIngredients->find($productData['product_id']);
            $quantity = $productData['quantity'];

            foreach ($product->ingredients as $ingredient) {
                $requiredAmount = $ingredient->pivot->amount * $quantity;

                if (!isset($ingredientUpdates[$ingredient->id])) {
                    $ingredientUpdates[$ingredient->id] = [
                        'ingredient' => $ingredient,
                        'totalRequired' => 0
                    ];
                }

                $ingredientUpdates[$ingredient->id]['totalRequired'] += $requiredAmount;

                if ($ingredient->quantity < $ingredientUpdates[$ingredient->id]['totalRequired']) {
                    throw new InsufficientIngredientsException("Insufficient stock for ingredient: {$ingredient->name}");
                }
            }
        }

        return $ingredientUpdates;
    }

    protected function attachProductsToOrder(Order $order, $productsWithIngredients, array $products): void
    {
        foreach ($products as $productData) {
            $product = $productsWithIngredients->find($productData['product_id']);
            $quantity = $productData['quantity'];

            $order->products()->attach($product->id, ['quantity' => $quantity]);
        }
    }
}
