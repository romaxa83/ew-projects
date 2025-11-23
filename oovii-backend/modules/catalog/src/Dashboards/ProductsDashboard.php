<?php

namespace WezomCms\Catalog\Dashboards;

use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;

class ProductsDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'products.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return Product::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-catalog::admin.products.Products');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-list';
    }

    /**
     * @return null|string
     */
    public function url(): ?string
    {
        return route('admin.products.index');
    }
}
