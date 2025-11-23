<?php

namespace WezomCms\Imports;

use WezomCms\Catalog\Helpers\GatesHelpers;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Facades\SidebarMenu;

class ImportServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {}

    public function boot(): void
    {
        parent::boot();
    }

    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->add(
            'imports',
            __('cms-imports::admin.imports'),
            [
                'view',
                'create',
                'delete' => GatesHelpers::modelGate('imports.delete'),
                'edit-settings',
            ]
        );
    }

    public function adminMenu()
    {
        SidebarMenu::add(__('cms-imports::admin.imports'), route('admin.imports.index'))
            ->data('permission', 'imports.view')
            ->data('icon', 'fa-download')
            ->data('position', 1)
            ->nickname('imports');
    }
}

