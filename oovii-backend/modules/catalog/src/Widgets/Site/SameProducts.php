<?php

namespace WezomCms\Catalog\Widgets\Site;

use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class SameProducts extends AbstractWidget
{
    /**
     * View name.
     *
     * @var string
     */
    protected $view = 'cms-catalog::site.widgets.products-carousel';

    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        /** @var Product $product */
        $product = $this->parameter('product');
        if (!$product) {
            return null;
        }

        $products = Product::published()
            ->whereKeyNot($product->id)
            ->where('category_id', $product->category_id)
            ->inRandomOrder()
            ->limit($this->parameter('limit', 20))
            ->get();

        if ($products->isEmpty()) {
            return null;
        }

        return [
            'result' => $products,
            'title' => $this->parameter('title', __('cms-catalog::site.products.Same products')),
        ];
    }
}
