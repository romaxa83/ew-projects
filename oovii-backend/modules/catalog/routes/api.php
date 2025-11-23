<?php

use WezomCms\Catalog\Http\Controllers\Api\V1;

Route::middleware('set.locale')->group(function(){
    // collection
    Route::get('mobile/collections', [V1\CollectionController::class, 'list'])
        ->name('api.v1.mobile.collections');

    Route::get('mobile/collections/{id}', [V1\CollectionController::class, 'show'])
        ->name('api.v1.mobile.collections.show');

    Route::post('mobile/collections/check-hashes', [V1\CollectionController::class, 'checkHash'])
        ->name('api.v1.mobile.collections.check-hashes');

    Route::get('mobile/collections-all_products', [V1\CollectionController::class, 'allProducts'])
        ->name('api.v1.mobile.collections.all-products');

    // brands
    Route::get('mobile/brands', [V1\BrandController::class, 'list'])
        ->name('api.v1.mobile.brands');

    // specifications
    Route::get('mobile/specifications', [V1\SpecificationController::class, 'list'])
        ->name('api.v1.mobile.specifications');

    // categories
    Route::get('mobile/categories', [V1\CategoryController::class, 'list'])
        ->name('api.v1.mobile.categories');

    // product
    Route::get('mobile/products', [V1\ProductController::class, 'list'])
        ->name('api.v1.mobile.products.list');

    Route::get('mobile/products/{id}', [V1\ProductController::class, 'show'])
        ->name('api.v1.mobile.products.show');

    Route::get('mobile/product-labels', [V1\ProductLabelsController::class, 'list'])
        ->name('api.v1.mobile.products.labels.list');

    Route::get('mobile/products-count', [V1\ProductController::class, 'count'])
        ->name('api.v1.mobile.products.count');

    Route::get('mobile/product-cost-range', [V1\ProductController::class, 'costRange'])
        ->name('api.v1.mobile.products.cost-range');

    Route::middleware('auth:api')->group(function(){
        Route::post('mobile/products/{id}/add-to-wishlist', [V1\ProductController::class, 'addToWishlist'])
            ->name('api.v1.mobile.products.add-to-wishlist');

        Route::post('mobile/products/{id}/remove-from-wishlist', [V1\ProductController::class, 'removeFromWishlist'])
            ->name('api.v1.mobile.products.remove-from-wishlist');

        Route::post('mobile/products/clear-wishlist', [V1\ProductController::class, 'clearWishlist'])
            ->name('api.v1.mobile.products.clear-wishlist');
    });
});
