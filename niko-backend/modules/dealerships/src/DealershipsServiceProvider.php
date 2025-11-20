<?php

namespace WezomCms\Dealerships;

use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Facades\SidebarMenu;
use WezomCms\Core\Traits\SidebarMenuGroupsTrait;

class DealershipsServiceProvider extends BaseServiceProvider
{
    use SidebarMenuGroupsTrait;

    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.dealerships.dealerships.dashboards';

    protected $translationKeys = [
        'cms-dealerships::admin.schedule.mon',
        'cms-dealerships::admin.schedule.tue',
        'cms-dealerships::admin.schedule.wed',
        'cms-dealerships::admin.schedule.thu',
        'cms-dealerships::admin.schedule.fri',
        'cms-dealerships::admin.schedule.sat',
        'cms-dealerships::admin.schedule.sun',
    ];

    public function boot()
    {
        \RouteRegistrar::apiRoutes($this->root('routes/api.php'));

        parent::boot();
    }

    /**
     * @param  PermissionsContainerInterface  $permissions
     */
    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->add('dealerships', __('cms-dealerships::admin.Dealerships'));
    }

    public function adminMenu()
    {
        SidebarMenu::add(__('cms-dealerships::admin.Dealerships'), route('admin.dealerships.index'))
            ->data('permission', 'dealerships.view')
            ->data('icon', 'fa-building')
            ->data('position', 41)
            ->nickname('dealerships');

//        $group = $this->contentGroup()
//            ->add(__('cms-regions::admin.Regions and cities'), route('admin.regions.index'))
//            ->data('icon', 'fa-list')
//            ->nickname('regions');
//
//        $group->add(__('cms-regions::admin.Regions'), route('admin.regions.index'))
//            ->data('permission', 'regions.view')
//            ->data('icon', 'fa-list')
//            ->data('position', 1);
//
//        $group->add(__('cms-regions::admin.Cities'), route('admin.cities.index'))
//            ->data('permission', 'cities.view')
//            ->data('icon', 'fa-list')
//            ->data('position', 3);
    }
}
