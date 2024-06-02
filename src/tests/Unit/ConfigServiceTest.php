<?php
namespace Tests\Unit;

use Closure;
use Tests\TestCase;
use App\Models\Config;
use App\Services\ConfigService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConfigServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
    }

    /** @test */
    public function it_gets_a_config_value_from_the_cache()
    {
        Config::factory()->create(['key' => 'merchant_email', 'value' => 'merchant@example.com']);

        Cache::shouldReceive('remember')
            ->once()
            ->with("config_merchant_email", 3600, Closure::class)
            ->andReturn('merchant@example.com');

        $service = new ConfigService();
        $value = $service->get('merchant_email');

        $this->assertEquals('merchant@example.com', $value);
    }

    /** @test */
    public function it_gets_a_config_value_from_the_database_and_caches_it()
    {
        Config::factory()->create(['key' => 'merchant_email', 'value' => 'merchant@example.com']);

        Cache::shouldReceive('remember')
            ->once()
            ->with("config_merchant_email", 3600, Closure::class)
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $service = new ConfigService();
        $value = $service->get('merchant_email');

        $this->assertEquals('merchant@example.com', $value);
    }
}
