<?php

namespace WezomCms\Providers;

use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Facades\SidebarMenu;
use WezomCms\Core\Traits\SidebarMenuGroupsTrait;
use WezomCms\Providers\Repositories\ProviderRepository;
use WezomCms\Providers\Types\ProviderStatus;

class ProvidersServiceProvider extends BaseServiceProvider
{
    use SidebarMenuGroupsTrait;

    protected $dashboard = 'cms.providers.providers.dashboards';

    public function boot()
    {
        parent::boot();
    }

    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->add('providers', __('cms-providers::admin.provider.Providers'))
            ->withEditSettings();
    }

    public function adminMenu()
    {
        $status = ProviderStatus::createDraft();
        $count = app(ProviderRepository::class)->countByStatus($status);

        SidebarMenu::add(__('cms-providers::admin.provider.Providers'), route('admin.providers.index'))
            ->data('permission', 'providers.view')
            ->data('icon', 'fa-bus')
            ->data('badge', $count)
            ->data('badge_type', 'warning')
            ->data('position', 1)
            ->nickname('providers');

//        $group = SidebarMenu::add(__('cms-providers::admin.provider.Providers'), route('admin.providers.index'))
//            ->data('icon', 'fa-list')
//            ->nickname('providers');
//
//        $group->data('badge', $count)->data('badge_type', 'warning');
//
//        $group->add(__('cms-providers::admin.provider.Providers'), route('admin.providers.index'))
//            ->data('permission', 'providers.view')
//            ->data('icon', 'fa-bus')
//            ->data('badge', $count)
//            ->data('badge_type', 'warning')
//            ->data('position', 1);
//
//        $group->add(__('cms-providers::admin.company.Companies'), route('admin.companies.index'))
//            ->data('permission', 'companies.view')
//            ->data('icon', 'fa-building')
//            ->data('position', 2);
    }
}

