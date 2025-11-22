<?php

namespace Tests\Feature\Saas\Admins;

use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class AdminDeleteTest extends TestCase
{
    use DatabaseTransactions;
    use AdminFactory;
    use PermissionFactory;

    public function test_not_auth_admin_cant_delete_admin(): void
    {
        $admin = $this->createAdmin();

        $this->deleteJson(route('v1.saas.admins.destroy', $admin))
            ->assertUnauthorized();
    }

    public function test_not_permitted_admin_cant_delete_admin(): void
    {
        $manager = $this->createAdmin();
        $this->loginAsSaasAdmin($manager);

        $admin = $this->createAdmin();

        $this->assertDatabaseHas(Admin::TABLE, ['id' => $admin->id]);

        $this->deleteJson(route('v1.saas.admins.destroy', $admin))
            ->assertForbidden();

        $this->assertDatabaseHas(Admin::TABLE, ['id' => $admin->id]);
    }

    public function test_permitted_admin_cant_delete_admin(): void
    {
        $manager = $this->createAdmin()->assignRole(
            $this->createRoleAdminDeleter()
        );

        $this->loginAsSaasAdmin($manager);

        $admin = $this->createAdmin();

        $this->assertDatabaseHas(Admin::TABLE, ['id' => $admin->id]);

        $this->deleteJson(route('v1.saas.admins.destroy', $admin))
            ->assertNoContent();

        $this->assertDatabaseMissing(Admin::TABLE, ['id' => $admin->id]);
    }
}
