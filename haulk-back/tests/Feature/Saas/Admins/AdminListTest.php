<?php

namespace Tests\Feature\Saas\Admins;

use App\Permissions\SimplePermission;

class AdminListTest extends BaseAdminManagerTest
{

    public function test_not_auth_admin_cant_get_list_of_admins(): void
    {
        $this->requestToAdminListRoute()->assertUnauthorized();
    }

    public function test_not_permitted_admin_cant_get_list_of_admins(): void
    {
        $this->loginAsSaasAdmin();

        $this->requestToAdminListRoute()->assertForbidden();
    }

    public function test_permitted_admin_cant_get_list_of_admins(): void
    {
        $manager = $this->createAdmin()->assignRole(
            $this->createRoleAdminLister()
        );

        $this->loginAsSaasAdmin($manager);

        $this->createAdmins(50);

        $response = $this->requestToAdminListRoute()
            ->assertOk();

        $admins = $response->json('data');

        self::assertCount(50, $admins);
    }

    public function test_list_item_has_permission(): void
    {
        $manager = $this->createAdmin()->assignRole(
            $this->createRoleAdminManager()
        );

        $this->loginAsSaasAdmin($manager);

        $this->createAdmins(5);

        $response = $this->requestToAdminListRoute()
            ->assertOk();

        $admins = $response->json('data');

        $admin = array_shift($admins);

        self::assertEquals(
            [
                SimplePermission::SHOW,
                SimplePermission::UPDATE,
                SimplePermission::DELETE,
            ],
            $admin['permission']
        );
    }
}
