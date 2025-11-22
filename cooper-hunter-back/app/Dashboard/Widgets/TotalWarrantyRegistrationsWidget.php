<?php

namespace App\Dashboard\Widgets;

use App\Enums\Dashboard\Widgets\DashboardWidgetSectionEnum;
use App\Enums\Dashboard\Widgets\DashboardWidgetTypeEnum;
use App\Models\Warranty\WarrantyRegistration;
use App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationListPermission;

class TotalWarrantyRegistrationsWidget extends AbstractWidget
{
    public const PERMISSION = WarrantyRegistrationListPermission::KEY;

    public function getTitle(): string
    {
        return __('dashboard.widgets.total_warranty_registrations');
    }

    public function getValue(): string
    {
        return WarrantyRegistration::query()
            ->count();
    }

    public function getSection(): DashboardWidgetSectionEnum
    {
        return DashboardWidgetSectionEnum::WARRANTY_REGISTRATIONS();
    }

    public function getType(): DashboardWidgetTypeEnum
    {
        return DashboardWidgetTypeEnum::TOTAL();
    }
}