<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ProvidersSeeder::class);
        $this->call(ProductsSeeder::class);
    }
}
