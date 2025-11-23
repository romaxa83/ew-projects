<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Catalog\Models\Product;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        if (!$products = Product::count()) {
            Product::factory()->count(5)->create();
        }
    }
}
