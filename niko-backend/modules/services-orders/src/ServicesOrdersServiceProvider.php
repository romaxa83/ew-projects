<?php

namespace WezomCms\ServicesOrders;

use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Services\ServicesServiceProvider;
use WezomCms\ServicesOrders\Models\ServicesOrder;

class ServicesOrdersServiceProvider extends BaseServiceProvider
{
    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.services-orders.services-orders.dashboards';

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
        $permissions->add('services-orders', __('cms-services-orders::admin.Service orders'))->withEditSettings();
        $permissions->add('services-orders-rates', __('cms-services-orders::admin.Service order rate'));
    }

    public function adminMenu()
    {
        $group = ServicesServiceProvider::makeAdminGroup();
        $count = ServicesOrder::notViewed()->notReject()->count();

        $group->data('badge', $count)->data('badge_type', 'warning');

        $group->add(__('cms-services-orders::admin.Service orders'), route('admin.services-orders.index'))
            ->data('permission', 'services-orders.view')
            ->data('icon', 'fa-envelope')
            ->data('badge', $count)
            ->data('badge_type', 'warning')
            ->data('position', 6);

        $group->add(__('cms-services-orders::admin.Service order rate'), route('admin.services-orders-rates.index'))
            ->data('permission', 'services-orders-rates.view')
            ->data('icon', 'fa-star')
            ->data('position', 7);

    }
}
