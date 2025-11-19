<?php

namespace Wezom\Admins\GraphQL\Queries\Back;

use Wezom\Admins\AdminConst;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Contracts\Permissions\PermissionGroup;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Permissions\Permission;
use Wezom\Core\Permissions\PermissionsManager;

final class BackPermissions extends BackFieldResolver
{
    public function resolve(Context $context): array
    {
        return app(PermissionsManager::class)->guard(Admin::GUARD)->getAll()
            ->flatMap(fn (PermissionGroup $group) => $group->getPermissions()->map(
                fn (Permission $permission) => $permission->getKey()
            ))->toArray();
    }

    public function ability(): string
    {
        return AdminConst::SUPER_ADMIN;
    }
}
