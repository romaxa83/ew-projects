<?php

namespace WezomCms\Cars\Dashboard;

use WezomCms\Cars\Models\Brand;
use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;

class BrandDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'car-brands.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return Brand::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-cars::admin.Brands');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-car';
    }

    /**
     * @return string]
     */
    public function iconColorClass(): string
    {
        return 'color-info';
    }

    /**
     * @return null|string
     */
    public function url(): ?string
    {
        return route('admin.car-brands.index');
    }
}


