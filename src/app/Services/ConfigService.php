<?php
namespace App\Services;

use App\Services\Interfaces\ConfigServiceInterface;
use Illuminate\Support\Facades\Cache;
use App\Models\Config;

class ConfigService implements ConfigServiceInterface
{
    public function get($key): string
    {
        return Cache::remember("config_{$key}", 3600, function() use ($key) {
            return Config::where('key', $key)->value('value');
        });
    }
}
