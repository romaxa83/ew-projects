<?php

namespace App\Dashboard\Widgets;

use App\Contracts\Roles\HasGuardUser;
use App\Enums\Dashboard\Widgets\DashboardWidgetSectionEnum;
use App\Enums\Dashboard\Widgets\DashboardWidgetTypeEnum;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderListPermission;
use App\Services\Orders\OrderService;

class NewOrdersWidget extends AbstractWidget
{
    public const PERMISSION = OrderListPermission::KEY;

    public function __construct(protected OrderService $service)
    {
    }

    public function getTitle(): string
    {
        return __('dashboard.widgets.new_orders');
    }

    public function getValue(): string
    {
        /** @var HasGuardUser $user */
        if ($user = $this->user) {
            return $this->service->getCounterData($user)['created'];
        }

        return Order::query()->count();
    }

    public function getSection(): DashboardWidgetSectionEnum
    {
        return DashboardWidgetSectionEnum::ORDERS();
    }

    public function getType(): DashboardWidgetTypeEnum
    {
        return DashboardWidgetTypeEnum::NEW();
    }
}