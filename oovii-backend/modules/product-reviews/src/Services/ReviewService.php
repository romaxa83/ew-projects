<?php

namespace WezomCms\ProductReviews\Services;

use WezomCms\ProductReviews\Dto\ReviewDto;
use WezomCms\ProductReviews\Models\ProductReview;

class ReviewService
{
    public function create(ReviewDto $dto): ProductReview
    {
        $model = new ProductReview();
        $model->published = true;
        $model->product_id = $dto->productId;
        $model->user_id = $dto->userId;
        $model->name = $dto->name;
        $model->email = $dto->email;
        $model->parent_id = $dto->parentId;
        $model->text = $dto->text;
        $model->rating = $dto->rating;
        $model->like = $dto->like;

        $model->save();

        return $model;
    }
}
