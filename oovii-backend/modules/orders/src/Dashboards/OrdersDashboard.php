<?php

namespace WezomCms\Orders\Dashboards;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\Orders\Models\Order;

class OrdersDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'orders.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return Order::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-orders::admin.orders.Orders');
    }

    /**
     * @return string
     */
    protected function icon(): string
    {
        return 'fa-shopping-cart';
    }

    protected function iconColorClass(): string
    {
        return 'color-warning';
    }

    /**
     * @return null|string
     */
    protected function url(): ?string
    {
        return route('admin.orders.index');
    }
}
