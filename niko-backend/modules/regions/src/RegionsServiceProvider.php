<?php

namespace WezomCms\Regions;

use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Traits\SidebarMenuGroupsTrait;

class RegionsServiceProvider extends BaseServiceProvider
{
	use SidebarMenuGroupsTrait;

    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.regions.regions.dashboards';

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
		$permissions->add('regions', __('cms-regions::admin.Regions'));
		$permissions->add('cities', __('cms-regions::admin.Cities'));
	}

	public function adminMenu()
	{
        $group = $this->contentGroup()
            ->add(__('cms-regions::admin.Regions and cities'), route('admin.regions.index'))
            ->data('icon', 'fa-list')
            ->nickname('regions');

        $group->add(__('cms-regions::admin.Regions'), route('admin.regions.index'))
            ->data('permission', 'regions.view')
            ->data('icon', 'fa-list')
            ->data('position', 1);

        $group->add(__('cms-regions::admin.Cities'), route('admin.cities.index'))
            ->data('permission', 'cities.view')
            ->data('icon', 'fa-list')
            ->data('position', 3);
	}
}
