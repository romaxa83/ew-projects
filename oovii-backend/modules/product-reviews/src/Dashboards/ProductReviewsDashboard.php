<?php

namespace WezomCms\ProductReviews\Dashboards;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\ProductReviews\Models\ProductReview;

class ProductReviewsDashboard extends AbstractValueDashboard
{
    /**
     * @var null|string - permission for link
     */
    protected $ability = 'product-reviews.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return ProductReview::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-product-reviews::admin.Product reviews');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-comments';
    }

    /**
     * @return null|string
     */
    public function url(): ?string
    {
        return route('admin.product-reviews.index');
    }
}
