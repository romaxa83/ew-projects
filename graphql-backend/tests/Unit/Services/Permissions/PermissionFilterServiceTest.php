<?php

namespace Tests\Unit\Services\Permissions;

use App\Models\Companies\Company;
use App\Models\Permissions\Permission;
use App\Models\Users\User;
use App\Permissions\Users\UserCreatePermission;
use App\Permissions\Users\UserListPermission;
use Core\Services\Permissions\PermissionFilterService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class PermissionFilterServiceTest extends TestCase
{
    use DatabaseTransactions;
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
        $user = User::factory()
            ->withCompany(Company::factory()->create())
            ->create();

        $permissions = collect(
            [
                Permission::create(['name' => UserCreatePermission::KEY]),
                Permission::create(['name' => UserListPermission::KEY]),
            ]
        );

        self::assertCount(
            2,
            $permissions = $this->service->filter($user, $permissions)
        );

        self::assertTrue(
            $permissions->where('name', UserListPermission::KEY)->isNotEmpty()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(\Core\Services\Permissions\PermissionFilterService::class);

        Config::set('grants.filter_enabled', true);
    }
}
