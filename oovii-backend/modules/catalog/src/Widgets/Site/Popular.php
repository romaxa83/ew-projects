<?php

namespace WezomCms\Catalog\Widgets\Site;

use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class Popular extends AbstractWidget
{
    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        $products = Product::published()
            ->where('popular', true)
            ->inRandomOrder()
            ->limit(20)
            ->get();

        if ($products->isEmpty()) {
            return null;
        }

        $title = __('cms-catalog::site.products.Popular products');

        return ['result' => $products, 'title' => $title];
    }
}
