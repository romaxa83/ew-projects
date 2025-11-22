<?php

namespace App\Services\Dashboard;

use App\Collections\Dashboard\Widgets\DashboardWidgetsCollection;
use App\Dashboard\Widgets\AbstractWidget;
use App\Models\Admins\Admin;

class DashboardService
{
    public function widgets(Admin $admin): DashboardWidgetsCollection
    {
        $widgets = DashboardWidgetsCollection::make([]);

        foreach (config('dashboard.widgets') as $widgetClass) {
            if (!is_a($widgetClass, AbstractWidget::class, true)) {
                continue;
            }

            $widget = $widgetClass::buildFor($admin);

            if ($widget->authorize()) {
                $widgets->push($widget);
            }
        }

        return $widgets;
    }
}