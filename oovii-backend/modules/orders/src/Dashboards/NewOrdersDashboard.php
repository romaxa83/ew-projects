<?php

namespace WezomCms\Orders\Dashboards;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\Orders\Models\Order;

class NewOrdersDashboard extends AbstractValueDashboard
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
        return Order::new()->count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-orders::admin.orders.New orders');
    }

    /**
     * @return string
     */
    protected function icon(): string
    {
        return 'fa-cart-plus';
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
        return route('admin.orders.index', ['status' => 1]);
    }
}
