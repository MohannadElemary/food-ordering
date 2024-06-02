<?php
namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use App\Services\NotificationService;
use App\Services\Interfaces\ConfigServiceInterface;
use Illuminate\Support\Facades\Mail;
use App\Mail\StockAlertMail;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_sends_stock_alert_email()
    {
        $ingredients = Ingredient::factory()->count(3)->make()->toArray();

        $configService = Mockery::mock(ConfigServiceInterface::class);
        $configService->shouldReceive('get')
            ->with('merchant_email')
            ->andReturn('merchant@example.com');

        Mail::fake();

        $notificationService = new NotificationService($configService);

        $notificationService->sendStockAlert($ingredients);

        Mail::assertQueued(StockAlertMail::class, function ($mail) use ($ingredients) {
            return $mail->hasTo('merchant@example.com') &&
                $mail->ingredients == $ingredients;
        });
    }
}
