<?php
namespace App\Services\Interfaces;

use App\Models\Config;

interface ConfigServiceInterface
{
    public function get(string $key): string;
}
