<?php

use App\Models\Admins\Admin;
use App\Models\Permissions\Permission;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Permissions\BasePermission;
use App\Services\Permissions\PermissionService;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminPermissionsTableSeeder extends Seeder
{
    /**
     * @var PermissionService
     */
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function run(): void
    {
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $groups = $this->permissionService->getFlattenPermissions(Admin::GUARD);

            $permissions = $groups
                ->flatten()
                ->map(
                    fn(BasePermission $basePermission) => [
                        'name' => $basePermission->getKey(),
                        'guard_name' => Admin::GUARD
                    ]
                )
                ->toArray();
            $superRole = Role::query()->firstOrCreate(
                ['name' => User::SUPERADMIN_ROLE, 'guard_name' => Admin::GUARD]
            );

            Permission::query()->upsert($permissions, ['name', 'guard_name']);

            $permissionsToAssign = Permission::query()->where('guard_name', Admin::GUARD)->get();

            $superRole->givePermissionTo($permissionsToAssign);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
