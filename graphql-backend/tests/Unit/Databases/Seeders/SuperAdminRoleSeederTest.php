<?php

declare(strict_types=1);

namespace Tests\Unit\Databases\Seeders;

use App\Models\Admins\Admin;
use App\Models\Permissions\Permission;
use Core\Services\Permissions\PermissionService;
use Database\Seeders\SuperAdminRoleSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SuperAdminRoleSeederTest extends TestCase
{
    use DatabaseTransactions;

    private PermissionService $permissionService;

    public function test_it_super_admin_role_has_all_permissions(): void
    {
        app(SuperAdminRoleSeeder::class)->run();

        self::assertEquals(
            $this->permissionService
                ->getPermissionsList(Admin::GUARD)
                ->count(),
            Permission::query()
                ->whereGuardName(Admin::GUARD)
                ->count()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->permissionService = $this->app->make(PermissionService::class);
    }
}
