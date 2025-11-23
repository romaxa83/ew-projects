<?php

namespace WezomCms\ProductReviews\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\ProductReviews\Models\ProductReview;

class ProductReviewsRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return ProductReview::query();
    }
}
