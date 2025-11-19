<?php

declare(strict_types=1);

namespace Wezom\Admins\Traits;

use Wezom\Admins\Models\Admin;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Permissions\Ability;
use Wezom\Core\Traits\Permissions\RoleHelperHelperTrait;

trait AdminTestTrait
{
    use RoleHelperHelperTrait;

    protected function loginAsAdmin(?Admin $admin = null): Admin
    {
        if (!$admin) {
            $admin = Admin::factory()->create();
        }
        $this->actingAs($admin, Admin::GUARD);

        return $admin;
    }

    protected function loginAsSuperAdmin(): Admin
    {
        return $this->loginAsAdmin()->assignRole(
            Role::unguarded(fn () => Role::create([
                'name' => 'Super Admin',
                'system_type' => RoleEnum::SUPER_ADMIN,
                'guard_name' => Admin::GUARD,
            ]))
        );
    }

    protected function loginAsAdminWithPermissions(
        array $permissions = [],
        ?Admin $admin = null,
        ?string $roleName = null
    ): Admin {
        return $this->loginAsAdmin($admin)->assignRole(
            $this->generateRole(
                $roleName ?? 'Admins role',
                count($permissions) ? $permissions : [
                    'admins.create',
                    'admins.view',
                    'admins.update',
                    'admins.delete',
                ],
                Admin::GUARD
            )
        );
    }

    protected function loginAsAdminWithAbility(Ability ...$abilities): Admin
    {
        $abilities = array_map(static fn (Ability $a) => $a->build(), $abilities);

        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole(
                    'Admins role',
                    $abilities,
                    Admin::GUARD
                )
            );
    }
}
