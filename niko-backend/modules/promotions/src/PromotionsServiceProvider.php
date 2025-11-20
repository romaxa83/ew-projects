<?php

namespace WezomCms\Promotions;

use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Facades\SidebarMenu;
use WezomCms\Core\Traits\SidebarMenuGroupsTrait;

class PromotionsServiceProvider extends BaseServiceProvider
{
    use SidebarMenuGroupsTrait;

    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.promotions.promotions.dashboards';


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
        $permissions->add('promotions', __('cms-promotions::admin.Promotions'));
    }

    public function adminMenu()
    {
        SidebarMenu::add(__('cms-promotions::admin.Promotions'), route('admin.promotions.index'))
            ->data('permission', 'promotions.view')
            ->data('icon', 'fa-shopping-bag')
            ->data('position', 41)
            ->nickname('promotions');
    }
}
