<?php

namespace App\Dashboard\Widgets;

use App\Enums\Dashboard\Widgets\DashboardWidgetSectionEnum;
use App\Enums\Dashboard\Widgets\DashboardWidgetTypeEnum;
use App\Models\Support\SupportRequest;
use App\Permissions\SupportRequests\SupportRequestListPermission;

class TotalSupportRequestsWidget extends AbstractWidget
{
    public const PERMISSION = SupportRequestListPermission::KEY;

    public function getTitle(): string
    {
        return __('dashboard.widgets.total_support_requests');
    }

    public function getValue(): string
    {
        return SupportRequest::query()
            ->count();
    }

    public function getSection(): DashboardWidgetSectionEnum
    {
        return DashboardWidgetSectionEnum::SUPPORT_REQUESTS();
    }

    public function getType(): DashboardWidgetTypeEnum
    {
        return DashboardWidgetTypeEnum::TOTAL();
    }
}