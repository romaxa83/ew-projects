<?php

namespace WezomCms\Cars\Dashboard;

use WezomCms\Cars\Models\Model;
use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;

class ModelDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'car-models.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return Model::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-cars::admin.Models');
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
        return route('admin.car-models.index');
    }
}



