<?php

namespace WezomCms\ServicesOrders\Dashboards;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\ServicesOrders\Models\ServicesOrder;

class ServicesOrdersDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'services-orders.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return ServicesOrder::notReject()->count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-services-orders::admin.Service orders');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-bell-o';
    }

    /**
     * @return string
     */
    public function iconColorClass(): string
    {
        return 'color-warning';
    }

    /**
     * @return null|string
     */
    public function url(): ?string
    {
        return route('admin.services-orders.index');
    }
}
