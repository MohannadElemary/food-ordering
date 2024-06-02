<?php
namespace Tests\Feature;

use App\Exceptions\InsufficientIngredientsException;
use DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\Ingredient;
use App\Services\OrderService;
use App\Services\StockService;
use App\Services\NotificationService;
use App\Services\ConfigService;
use App\Mail\StockAlertMail;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Cache::clear();

        DB::table('configs')->insert([
            'key' => 'merchant_email',
            'value' => 'merchant@example.com'
        ]);
    }

    /** @test */
    public function it_handles_the_full_order_workflow_successfully()
    {
        $ingredient1 = Ingredient::factory()->create(['quantity' => 20000, 'initial_stock' => 20000, 'alert_sent' => false]);
        $ingredient2 = Ingredient::factory()->create(['quantity' => 5000, 'initial_stock' => 5000, 'alert_sent' => false]);
        $ingredient3 = Ingredient::factory()->create(['quantity' => 1000, 'initial_stock' => 1000, 'alert_sent' => false]);

        $product = Product::factory()->create();
        $product->ingredients()->attach($ingredient1->id, ['amount' => 150]);
        $product->ingredients()->attach($ingredient2->id, ['amount' => 30]);
        $product->ingredients()->attach($ingredient3->id, ['amount' => 20]);

        $configService = new ConfigService();
        $stockService = new StockService();
        $notificationService = new NotificationService($configService);
        $orderService = new OrderService($configService, $stockService, $notificationService);

        $orderService->createOrder([['product_id' => $product->id, 'quantity' => 25]]);

        $this->assertDatabaseHas('orders', ['id' => 1]);
        $this->assertDatabaseHas('order_products', ['order_id' => 1, 'product_id' => $product->id, 'quantity' => 25]);

        $this->assertDatabaseHas('ingredients', ['id' => $ingredient1->id, 'quantity' => 16250]); // 150g * 25 = 3750g consumed, 20000 - 3750 = 16250
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient2->id, 'quantity' => 4250]); // 30g * 25 = 750g consumed, 5000 - 750 = 4250
        $this->assertDatabaseHas('ingredients', ['id' => $ingredient3->id, 'quantity' => 500]); // 20g * 25 = 500g consumed, 1000 - 500 = 500

        Mail::assertQueued(StockAlertMail::class, function ($mail) {
            return $mail->hasTo('merchant@example.com');
        });
    }

    /** @test */
    public function it_handles_insufficient_stock_properly()
    {
        $ingredient = Ingredient::factory()->create(['quantity' => 100, 'initial_stock' => 100, 'alert_sent' => false]);

        $product = Product::factory()->create();
        $product->ingredients()->attach($ingredient->id, ['amount' => 150]);

        $configService = new ConfigService();
        $stockService = new StockService();
        $notificationService = new NotificationService($configService);
        $orderService = new OrderService($configService, $stockService, $notificationService);

        $this->expectException(InsufficientIngredientsException::class);

        $orderService->createOrder([['product_id' => $product->id, 'quantity' => 2]]);

        $this->assertDatabaseMissing('orders', ['id' => 1]);
    }

    /** @test */
    public function it_does_not_send_multiple_alert_emails_for_same_ingredient()
    {
        $ingredient = Ingredient::factory()->create(['quantity' => 20000, 'initial_stock' => 20000, 'alert_sent' => false]);

        $product = Product::factory()->create();
        $product->ingredients()->attach($ingredient->id, ['amount' => 150]);

        $configService = new ConfigService();
        $stockService = new StockService();
        $notificationService = new NotificationService($configService);
        $orderService = new OrderService($configService, $stockService, $notificationService);

        $orderService->createOrder([['product_id' => $product->id, 'quantity' => 67]]); // Consumes 10050g, leaves 9950g, below 50%

        $orderService->createOrder([['product_id' => $product->id, 'quantity' => 1]]); // Further consumption

        $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id, 'alert_sent' => true]);

        Mail::assertQueued(StockAlertMail::class, 1);
    }
}
