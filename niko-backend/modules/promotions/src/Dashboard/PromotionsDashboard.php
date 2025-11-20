<?php

namespace WezomCms\Promotions\Dashboard;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\Promotions\Models\Promotions;

class PromotionsDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'promotions.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return Promotions::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-promotions::admin.Promotions');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-shopping-bag';
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
        return route('admin.promotions.index');
    }
}



