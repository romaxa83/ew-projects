<?php

namespace WezomCms\Core\Foundation\Dashboard;

abstract class AbstractChartDashboard extends AbstractDashboardWidget
{
    public $view = 'cms-core::admin.dashboard.widgets.chart';

    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime;

    /**
     * @return string
     */
    public function render()
    {
        return '';
    }
}
