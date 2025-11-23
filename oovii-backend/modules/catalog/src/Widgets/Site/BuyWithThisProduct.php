<?php

namespace WezomCms\Catalog\Widgets\Site;

use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class BuyWithThisProduct extends AbstractWidget
{
    /**
     * @var int
     */
    protected $limit = 20;

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
        $product = $this->parameter('product');
        if (!$product) {
            return null;
        }

        /** @var Category|null $category */
        $category = $product->category;

        if (!$category) {
            return null;
        }

        $similarCategory = $category->similarCategories()->pluck('id');

        $products = Product::published()
            ->where('id', '<>', $product->id)
            ->whereIn('category_id', $similarCategory)
            ->inRandomOrder()
            ->limit($this->parameter('limit', $this->limit))
            ->get();

        if ($products->isEmpty()) {
            return null;
        }

        return [
            'result' => $products,
            'title' => $this->parameter('title', __('cms-catalog::site.products.With this product buy')),
        ];
    }
}
