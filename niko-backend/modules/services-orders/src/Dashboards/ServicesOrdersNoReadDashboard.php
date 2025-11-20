<?php

namespace WezomCms\ServicesOrders\Dashboards;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\ServicesOrders\Models\ServicesOrder;

class ServicesOrdersNoReadDashboard extends AbstractValueDashboard
{
    /**
     * @var null|string - permission for link
     */
    protected $ability = 'services-orders.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return ServicesOrder::unread()->notReject()->count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-services-orders::admin.Service orders new');
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
        return route('admin.services-orders.index', ['read' => 0]);
    }
}
