<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use WezomCms\Catalog\Models\Product;
use WezomCms\ProductReviews\Models\ProductReview;

class ProductReviewsSeeder extends Seeder
{
    public function run(): void
    {
        if (!$reviews = ProductReview::count()) {
            $products = Product::query()->limit(10)->get();

            $products->each(function (Product $product) {
                ProductReview::factory()
                    ->count(5)
                    ->create([
                        'product_id' => $product->id,
                        'parent_id' => null,
                    ]);
            });
        }
    }
}
