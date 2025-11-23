<?php

namespace WezomCms\Catalog\Widgets\Site;

use WezomCms\Catalog\Models\ViewedProducts;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class Viewed extends AbstractWidget
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
        $products = ViewedProducts::getProducts();

        if ($products->isEmpty()) {
            return null;
        }

        return [
            'result' => $products,
            'title' => $this->parameter('title', __('cms-catalog::site.products.Viewed products')),
        ];
    }
}
