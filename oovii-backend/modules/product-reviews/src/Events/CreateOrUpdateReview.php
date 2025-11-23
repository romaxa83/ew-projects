<?php

namespace WezomCms\ProductReviews\Events;

use Illuminate\Queue\SerializesModels;
use WezomCms\ProductReviews\Models\ProductReview;

class CreateOrUpdateReview
{
    use SerializesModels;

    public function __construct(public ProductReview $model)
    {}
}
