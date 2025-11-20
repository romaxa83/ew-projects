<?php

namespace WezomCms\Regions\Dashboard;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\Regions\Models\City;

class CityDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'cities.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return City::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-regions::admin.Cities');
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
        return route('admin.cities.index');
    }
}

