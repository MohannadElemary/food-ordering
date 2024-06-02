<?php
namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use App\Services\OrderService;
use App\Services\Interfaces\ConfigServiceInterface;
use App\Services\Interfaces\StockServiceInterface;
use App\Services\Interfaces\NotificationServiceInterface;
use App\Models\Product;
use App\Models\Ingredient;
use App\Exceptions\InsufficientIngredientsException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_an_order_successfully()
    {
        $configService = Mockery::mock(ConfigServiceInterface::class);
        $stockService = Mockery::mock(StockServiceInterface::class);
        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        $orderService = new OrderService($configService, $stockService, $notificationService);

        $ingredient1 = Ingredient::factory()->create(['quantity' => 20000]);
        $ingredient2 = Ingredient::factory()->create(['quantity' => 5000]);
        $ingredient3 = Ingredient::factory()->create(['quantity' => 1000]);

        $product = Product::factory()->create();
        $product->ingredients()->attach($ingredient1->id, ['amount' => 150]);
        $product->ingredients()->attach($ingredient2->id, ['amount' => 30]);
        $product->ingredients()->attach($ingredient3->id, ['amount' => 20]);

        $stockService->shouldReceive('updateStock')->andReturnUsing(function ($ingredientUpdates) {
            foreach ($ingredientUpdates as $update) {
                $ingredient = $update['ingredient'];
                $ingredient->quantity -= $update['totalRequired'];
                $ingredient->save();
            }
            return [];
        });

        $orderService->createOrder([['product_id' => $product->id, 'quantity' => 2]]);

        $this->assertDatabaseHas('orders', ['id' => 1]);
        $this->assertDatabaseHas('order_products', ['order_id' => 1, 'product_id' => $product->id, 'quantity' => 2]);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient1->id, 'quantity' => 19700]);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient2->id, 'quantity' => 4940]);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient3->id, 'quantity' => 960]);
    }

    /** @test */
    public function it_throws_error_for_insufficient_stock()
    {
        $this->expectException(InsufficientIngredientsException::class);

        $configService = Mockery::mock(ConfigServiceInterface::class);
        $stockService = Mockery::mock(StockServiceInterface::class);
        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        $orderService = new OrderService($configService, $stockService, $notificationService);

        $ingredient = Ingredient::factory()->create(['quantity' => 100]);

        $product = Product::factory()->create();
        $product->ingredients()->attach($ingredient->id, ['amount' => 150]);

        $stockService->shouldReceive('updateStock')->andReturnUsing(function ($ingredientUpdates) {
            foreach ($ingredientUpdates as $update) {
                if ($update['totalRequired'] > $update['ingredient']->quantity) {
                    throw new InsufficientIngredientsException("Insufficient stock for ingredient: {$update['ingredient']->name}");
                }
            }
            return [];
        });

        $orderService->createOrder([['product_id' => $product->id, 'quantity' => 2]]);
    }

    /** @test */
    public function it_sends_stock_alert()
    {
        $configService = Mockery::mock(ConfigServiceInterface::class);
        $stockService = Mockery::mock(StockServiceInterface::class);
        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        $orderService = new OrderService($configService, $stockService, $notificationService);

        $ingredient = Ingredient::factory()->create(['quantity' => 20000]);

        $product = Product::factory()->create();
        $product->ingredients()->attach($ingredient->id, ['amount' => 150]);

        $lowStockIngredients = [$ingredient];
        $stockService->shouldReceive('updateStock')->andReturnUsing(function ($ingredientUpdates) use ($lowStockIngredients) {
            foreach ($ingredientUpdates as $update) {
                $ingredient = $update['ingredient'];
                $ingredient->quantity -= $update['totalRequired'];
                $ingredient->save();

                if ($ingredient->quantity <= ($ingredient->initial_stock * 0.5) && !$ingredient->alert_sent) {
                    $lowStockIngredients[] = $ingredient;
                }
            }
            return $lowStockIngredients;
        });

        $notificationService->shouldReceive('sendStockAlert')->with($lowStockIngredients)->once();

        $orderService->createOrder([['product_id' => $product->id, 'quantity' => 2]]);

        $this->assertDatabaseHas('orders', ['id' => 1]);
        $this->assertDatabaseHas('order_products', ['order_id' => 1, 'product_id' => $product->id, 'quantity' => 2]);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id, 'quantity' => 19700]);
    }
}
