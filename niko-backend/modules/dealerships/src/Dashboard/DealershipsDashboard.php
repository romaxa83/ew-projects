<?php

namespace WezomCms\Dealerships\Dashboard;

use WezomCms\Cars\Models\Brand;
use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\Dealerships\Models\Dealership;

class DealershipsDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'dealerships.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return Dealership::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-dealerships::admin.Dealerships');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-building';
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
        return route('admin.dealerships.index');
    }
}



