<?php

namespace WezomCms\Core\Foundation\Dashboard;

trait RegisterDashboardWidgetsTrait
{
    /**
     * Register all dashboard.
     * @param  string|array  $widgets  - Array with dashboard widgets or array with config path's or config path.
     */
    public function registerDashboard($widgets)
    {
        /** @var DashboardContainer $dashboard */
        $dashboard = app(DashboardContainer::class);

        foreach ((array) $widgets as $key => $item) {
            $items = is_array($item) ? $item : config()->get($item, []);

            foreach ($items as $class) {
                $dashboard->addWidget($class);
            }
        }
    }
}
