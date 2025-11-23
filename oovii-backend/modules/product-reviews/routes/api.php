<?php

use WezomCms\ProductReviews\Http\Controllers\Api\V1\ReviewController;

Route::middleware('set.locale')->group(function(){

    Route::middleware('auth:api')->group(function(){
        Route::post('mobile/products/{id}/review', [ReviewController::class, 'create'])
            ->name('api.v1.mobile.product.review.create');
    });
});
