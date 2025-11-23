<?php

namespace WezomCms\Catalog\Dashboards;

use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;

class CategoriesDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'categories.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return Category::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-catalog::admin.categories.Categories');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-object-group';
    }

    /**
     * @return null|string
     */
    public function url(): ?string
    {
        return route('admin.categories.index');
    }
}
