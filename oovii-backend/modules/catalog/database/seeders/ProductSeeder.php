<?php

namespace WezomCms\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Catalog\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        if(!Product::query()->first()){
            Product::factory(15)->create();
        }
    }
}
