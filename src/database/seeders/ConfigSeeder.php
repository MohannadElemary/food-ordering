<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('configs')->insert([
            'key' => 'merchant_email',
            'value' => 'merchant@example.com',
        ]);
    }
}
