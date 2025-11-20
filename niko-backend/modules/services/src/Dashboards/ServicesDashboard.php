<?php

namespace WezomCms\Services\Dashboards;

use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;
use WezomCms\Services\Models\Service;
use WezomCms\Services\Models\ServiceGroup;

class ServicesDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'services.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return ServiceGroup::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-services::admin.Services');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-briefcase';
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
        return route('admin.service-groups.index');
    }
}
