<?php

namespace WezomCms\Cars;

use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Facades\SidebarMenu;
use WezomCms\Core\Traits\SidebarMenuGroupsTrait;

class CarsServiceProvider extends BaseServiceProvider
{
    use SidebarMenuGroupsTrait;

    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.cars.cars.dashboards';

    protected $translationKeys = [];

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
        $permissions->add('car-brands', __('cms-cars::admin.Brands'));
        $permissions->add('car-models', __('cms-cars::admin.Models'));
        $permissions->add('car-transmissions', __('cms-cars::admin.Transmissions'));
        $permissions->add('car-engine-types', __('cms-cars::admin.Engine types'));
    }

    public function adminMenu()
    {
        $catalog = SidebarMenu::add(__('cms-cars::admin.Cars'), route('admin.car-brands.index'))
            ->data('icon', 'fa-car')
            ->data('position', 8)
            ->nickname('cars');

        $catalog->add(__('cms-cars::admin.Brands'), route('admin.car-brands.index'))
            ->data('permission', 'car-brands.view')
            ->data('icon', 'fa-ravelry')
            ->data('position', 2);
        $catalog->add(__('cms-cars::admin.Models'), route('admin.car-models.index'))
            ->data('permission', 'car-models.view')
            ->data('icon', 'fa-ravelry')
            ->data('position', 3);
        $catalog->add(__('cms-cars::admin.Transmissions'), route('admin.car-transmissions.index'))
            ->data('permission', 'car-transmissions.view')
            ->data('icon', 'fa-cogs')
            ->data('position', 4);
        $catalog->add(__('cms-cars::admin.Engine types'), route('admin.car-engine-types.index'))
            ->data('permission', 'car-engine-types.view')
            ->data('icon', 'fa-cogs')
            ->data('position', 5);
    }
}
