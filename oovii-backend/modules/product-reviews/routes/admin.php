<?php

use WezomCms\ProductReviews\Http\Controllers\Admin\ProductReviewsController;

Route::adminResource('product-reviews', ProductReviewsController::class)->settings();

Route::get('reviews-by-product-id/{id}/{exclude?}', [ProductReviewsController::class, 'reviewsByProductId'])
    ->name('reviews-by-product-id');

Route::get('product-reviews/export', [ProductReviewsController::class, 'export'])
    ->name('product-reviews.export');
