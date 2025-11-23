<?php

namespace WezomCms\ProductReviews\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Catalog\Models\Product;
use WezomCms\ProductReviews\Models\ProductReview;

class ProductReviewFactory extends Factory
{
    protected $model = ProductReview::class;

    public function definition()
    {
        $parentReview = ProductReview::inRandomOrder()->first();
        $adminAnswer = $parentReview ? $this->faker->boolean : false;

        return [
            'published' => true,
            'product_id' => optional(Product::inRandomOrder()->first())->id,
            'parent_id' => optional($parentReview)->id,
            'already_bought' => $adminAnswer ? false : $this->faker->boolean,
            'admin_answer' => $adminAnswer,
            'rating' => $this->faker->numberBetween(1, 5),
            'like' => $this->faker->boolean,
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'text' => $this->faker->realText($this->faker->numberBetween(100, 250)),
        ];
    }
}
