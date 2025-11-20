<?php

namespace Tests\Unit\Services\Permissions;

use App\Models\Employees\Employee;
use App\Models\Permissions\Permission;
use App\Permissions\Departments\CreatePermission;
use App\Permissions\Departments\ListPermission;
use App\Permissions\Departments\UpdatePermission;
use Core\Services\Permissions\PermissionFilterService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class PermissionFilterServiceTest extends TestCase
{
    use RoleHelperHelperTrait;

    protected PermissionFilterService $service;

    public function test_get_service_as_singleton(): void
    {
        self::assertEquals(
            spl_object_id($this->service),
            spl_object_id(app(\Core\Services\Permissions\PermissionFilterService::class))
        );
    }

    public function test_user_get_empty_permissions(): void
    {
        $user = Employee::factory()
            ->create();

        $permissions = collect(
            [
                Permission::create(['name' => 'perm']),
                Permission::create(['name' => 'perm_1']),
            ]
        );

        self::assertCount(
            2,
            $permissions = $this->service->filter($user, $permissions)
        );

        self::assertTrue(
            $permissions->where('name', 'perm')->isNotEmpty()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(\Core\Services\Permissions\PermissionFilterService::class);

        Config::set('grants.filter_enabled', true);
    }
}
