<?php

namespace Database\Seeders\Catalog\Products;

use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductTranslation;
use Illuminate\Database\Seeder;

class ProductsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->whereDoesntHave('children')->get();

        foreach ($categories->chunk(10) as $chunk) {
            foreach ($chunk as $category) {
                Product::factory()
                    ->times(5)
                    ->has(
                        ProductTranslation::factory()
                            ->times(2)
                            ->sequence(
                                ['language' => 'en'],
                                ['language' => 'es'],
                            ),
                        'translations'
                    )
                    ->state(
                        [
                            'category_id' => $category->id
                        ]
                    )
                    ->create();
            }
        }
    }
}
