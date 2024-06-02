<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use App\Models\Ingredient;
use App\Models\Product;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDatabase();
    }

    protected function seedDatabase(): void
    {
        $beef = Ingredient::create(['name' => 'Beef', 'quantity' => 20000, 'initial_stock' => 20000, 'alert_sent' => false]);
        $cheese = Ingredient::create(['name' => 'Cheese', 'quantity' => 5000, 'initial_stock' => 5000, 'alert_sent' => false]);
        $onion = Ingredient::create(['name' => 'Onion', 'quantity' => 1000, 'initial_stock' => 1000, 'alert_sent' => false]);

        $burger = Product::create(['name' => 'Burger', 'description' => 'Delicious beef burger', 'price' => 5.99]);
        $burger->ingredients()->attach($beef->id, ['amount' => 150]);
        $burger->ingredients()->attach($cheese->id, ['amount' => 30]);
        $burger->ingredients()->attach($onion->id, ['amount' => 20]);
    }

    /** @test */
    public function it_creates_an_order_successfully()
    {
        $response = $this->postJson('/api/orders', [
            'products' => [
                ['product_id' => 1, 'quantity' => 2]
            ]
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Order placed successfully']);

        $this->assertDatabaseHas('orders', ['id' => 1]);
        $this->assertDatabaseHas('order_products', ['order_id' => 1, 'product_id' => 1, 'quantity' => 2]);

        $this->assertDatabaseHas('ingredients', ['id' => 1, 'quantity' => 19700]); // 150g * 2 = 300g consumed
        $this->assertDatabaseHas('ingredients', ['id' => 2, 'quantity' => 4940]); // 30g * 2 = 60g consumed
        $this->assertDatabaseHas('ingredients', ['id' => 3, 'quantity' => 960]); // 20g * 2 = 40g consumed
    }

    /** @test */
    public function it_throws_error_for_insufficient_stock()
    {
        $response = $this->postJson('/api/orders', [
            'products' => [
                ['product_id' => 1, 'quantity' => Response::HTTP_OK] // Exceeding the stock
            ]
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(['error' => 'Insufficient stock for ingredient: Beef']);
    }

    /** @test */
    public function it_handles_rate_limiting()
    {
        // Simulate multiple requests to hit the rate limit
        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/orders', [
                'products' => [
                    ['product_id' => 1, 'quantity' => 1]
                ]
            ]);
        }

        $response = $this->postJson('/api/orders', [
            'products' => [
                ['product_id' => 1, 'quantity' => 1]
            ]
        ]);

        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS)
            ->assertJson(['message' => 'Too Many Attempts.']);
    }
}
