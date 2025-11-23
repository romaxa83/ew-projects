<?php

namespace WezomCms\ProductReviews\Widgets;

use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;
use WezomCms\Core\Models\Setting;
use WezomCms\ProductReviews\Models\ProductReview;

class LatestReviews extends AbstractWidget
{
    /**
     * A list of models that, when changed, will clear the cache of this widget.
     *
     * @var array
     */
    public static $models = [ProductReview::class, Product::class, Setting::class];

    /**
     * @return array|null
     * @throws \Exception
     */
    public function execute(): ?array
    {
        $reviewIds = ProductReview::select(\DB::raw('max(id) as id'))
            ->published()
            ->whereHas('product', published_scope())
            ->root()
            ->groupBy('product_id')
            ->pluck('id')
            ->toArray();

        rsort($reviewIds);

        $reviewIds = array_slice($reviewIds, 0, settings('product-reviews.site.widget_limit', 10));

        $reviews = ProductReview::whereIn('id', $reviewIds)
            ->latest('id')
            ->get();

        if ($reviews->isEmpty()) {
            return null;
        }

        return compact('reviews');
    }
}
