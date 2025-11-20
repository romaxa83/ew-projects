<?php

namespace WezomCms\Services;

use Illuminate\Database\Eloquent\Collection;
use SidebarMenu;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Contracts\SitemapXmlGeneratorInterface;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Services\Models\Service;

class ServicesServiceProvider extends BaseServiceProvider
{
    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.services.services.dashboards';

    public function boot()
    {
        \RouteRegistrar::apiRoutes($this->root('routes/api.php'));

        parent::boot();
    }

    /**
     * @return \Lavary\Menu\Item
     */
    public static function makeAdminGroup()
    {
        $group = SidebarMenu::get('services');
        if (!$group) {
            $group = SidebarMenu::add(__('cms-services::admin.Services'), route('admin.services.index'))
                ->data('icon', 'fa-briefcase')
                ->data('position', 30)
                ->nickname('services');
        }

        return $group;
    }

    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->add('services', __('cms-services::admin.Services'))->withEditSettings();
        $permissions->add('service-groups', __('cms-services::admin.Service groups'));
    }

    public function adminMenu()
    {
        $useGroups = config('cms.services.services.use_groups');
        if ($useGroups || Helpers::providerLoaded('WezomCms\ServicesOrders\ServicesOrdersServiceProvider')) {
            $group = static::makeAdminGroup();

            if ($useGroups) {
                $group->add(__('cms-services::admin.Groups'), route('admin.service-groups.index'))
                    ->data('permission', 'service-groups.view')
                    ->data('icon', 'fa-th-large')
                    ->data('position', 1);
            }

            $group->add(__('cms-services::admin.Services'), route('admin.services.index'))
                ->data('permission', 'services.view')
                ->data('icon', 'fa-list')
                ->data('position', 2);

        } else {
            SidebarMenu::add(__('cms-services::admin.Services'), route('admin.services.index'))
                ->data('permission', 'services.view')
                ->data('icon', 'fa-briefcase')
                ->data('position', 30)
                ->nickname('services');
        }
    }
}
