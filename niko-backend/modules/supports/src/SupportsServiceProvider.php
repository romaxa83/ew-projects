<?php

namespace WezomCms\Supports;

use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Facades\SidebarMenu;
use WezomCms\Core\Traits\SidebarMenuGroupsTrait;
use WezomCms\Supports\Models\Support;

class SupportsServiceProvider extends BaseServiceProvider
{
    use SidebarMenuGroupsTrait;

    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.supports.supports.dashboards';


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
        $permissions->add('supports', __('cms-supports::admin.Support'));
    }

    public function adminMenu()
    {
        $count = Support::unread()->count();

        SidebarMenu::add(__('cms-supports::admin.Support'), route('admin.supports.index'))
            ->data('permission', 'supports.view')
            ->data('icon', 'fa-cog')
            ->data('badge', $count)
            ->data('position',99)
            ->nickname('supports');
    }
}
