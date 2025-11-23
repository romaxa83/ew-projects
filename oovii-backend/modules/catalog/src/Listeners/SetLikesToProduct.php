<?php

namespace WezomCms\Catalog\Listeners;

use Throwable;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Catalog\Services\ProductService;
use WezomCms\ProductReviews\Events\CreateOrUpdateReview;

class SetLikesToProduct
{
    public function handle(CreateOrUpdateReview $event): void
    {
        try {
            if ($productID = $event->model->product_id) {
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
