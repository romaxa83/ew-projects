<?php

namespace App\Dashboard\Widgets;

use App\Enums\Dashboard\Widgets\DashboardWidgetSectionEnum;
use App\Enums\Dashboard\Widgets\DashboardWidgetTypeEnum;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderListPermission;

class TotalOrdersWidget extends AbstractWidget
{
    public const PERMISSION = OrderListPermission::KEY;

    public function getTitle(): string
    {
        return __('dashboard.widgets.total_orders');
    }

    public function getValue(): string
    {
        return Order::query()
            ->count();
    }

    public function getSection(): DashboardWidgetSectionEnum
    {
        return DashboardWidgetSectionEnum::ORDERS();
    }

    public function getType(): DashboardWidgetTypeEnum
    {
        return DashboardWidgetTypeEnum::TOTAL();
    }
}