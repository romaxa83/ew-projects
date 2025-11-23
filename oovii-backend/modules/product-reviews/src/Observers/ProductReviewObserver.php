<?php

namespace WezomCms\ProductReviews\Observers;

use Throwable;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Catalog\Services\ProductService;
use WezomCms\ProductReviews\Models\ProductReview;

/**
 * Class ProductObserver
 * @package WezomCms\ProductReviews\Observers
 */
class ProductReviewObserver
{
    /**
     * @param  ProductReview  $review
     */
    public function saved(ProductReview $review): void
    {
        if ($review->isRoot()) {
            $review->load([
                'product' => function ($query) {
                    $query->withTrashed();
                }
            ]);

            if ($review->product) {
                $this->calculateRating($review->product);
                $this->setLikesToProduct($review);
            }
        }
    }

    /**
     * @param  ProductReview  $review
     */
    public function deleted(ProductReview $review): void
    {
        if ($review->isRoot()) {
            $review->load([
                'product' => function ($query) {
                    $query->withTrashed();
                }
            ]);

            if ($review->product) {
                $this->calculateRating($review->product);
                $this->setLikesToProduct($review);
            }
        }
    }

    /**
     * @param  Product  $product
     */
    protected function calculateRating(Product $product): void
    {
        $rating = $product->rootReviews()->avg('rating');

        $product->rating = $rating ?: 0;

        $product->save();
    }

    protected function setLikesToProduct(ProductReview $review): void
    {
        try {
            if ($productID = $review->product_id) {
                $productRepo = app(ProductRepository::class);
                $productService = app(ProductService::class);

                $productService->updateLikes(
                    $productRepo->getOneBy('id', $productID)
                );
            }
        } catch (Throwable $e) {
            logger($e->getMessage());
        }
    }
}
