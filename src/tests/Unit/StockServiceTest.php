<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Ingredient;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_updates_stock_and_returns_low_stock_ingredients()
    {
        $ingredient1 = Ingredient::factory()->create(['quantity' => 20000, 'initial_stock' => 20000, 'alert_sent' => false]);
        $ingredient2 = Ingredient::factory()->create(['quantity' => 5000, 'initial_stock' => 5000, 'alert_sent' => false]);
        $ingredient3 = Ingredient::factory()->create(['quantity' => 1000, 'initial_stock' => 1000, 'alert_sent' => false]);

        $ingredientUpdates = [
            ['ingredient' => $ingredient1, 'totalRequired' => 15000],
            ['ingredient' => $ingredient2, 'totalRequired' => 2000],
            ['ingredient' => $ingredient3, 'totalRequired' => 600]
        ];

        $stockService = new StockService();

        $lowStockIngredients = $stockService->updateStock($ingredientUpdates);

        $this->assertCount(2, $lowStockIngredients);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient1->id, 'quantity' => 5000, 'alert_sent' => true]);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient2->id, 'quantity' => 3000, 'alert_sent' => false]);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient3->id, 'quantity' => 400, 'alert_sent' => true]);
    }

    /** @test */
    public function it_updates_stock_without_any_low_stock_ingredients()
    {
        $ingredient1 = Ingredient::factory()->create(['quantity' => 20000, 'initial_stock' => 20000, 'alert_sent' => false]);
        $ingredient2 = Ingredient::factory()->create(['quantity' => 5000, 'initial_stock' => 5000, 'alert_sent' => false]);
        $ingredient3 = Ingredient::factory()->create(['quantity' => 1000, 'initial_stock' => 1000, 'alert_sent' => false]);

        $ingredientUpdates = [
            ['ingredient' => $ingredient1, 'totalRequired' => 2000],
            ['ingredient' => $ingredient2, 'totalRequired' => 500],
            ['ingredient' => $ingredient3, 'totalRequired' => 100]
        ];

        $stockService = new StockService();

        $lowStockIngredients = $stockService->updateStock($ingredientUpdates);

        $this->assertCount(0, $lowStockIngredients);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient1->id, 'quantity' => 18000, 'alert_sent' => false]);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient2->id, 'quantity' => 4500, 'alert_sent' => false]);
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient3->id, 'quantity' => 900, 'alert_sent' => false]);
    }
}
