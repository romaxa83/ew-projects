<?php

namespace Tests\Feature\Saas\Admins;

use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminCreate;
use Illuminate\Http\Response;

class AdminListFilterRolesTest extends BaseAdminManagerTest
{

    public function test_get_validation_message_for_not_existing_roles(): void
    {
        $this->loginAsAdminManager();

        $response = $this->requestToAdminListRoute(['roles' => [1, 2, 3]])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertEquals('Role with this id is not exists.', $response->json('errors.0.title'));
    }

    public function test_get_admin_filter_by_roles(): void
    {
        $this->loginAsAdminManager();

        $role1 = $this->createRole('Role 1', [AdminCreate::KEY], Admin::GUARD);
        $role2 = $this->createRole('Role 2', [AdminCreate::KEY], Admin::GUARD);
        $role3 = $this->createRole('Role 3', [AdminCreate::KEY], Admin::GUARD);

        $this->createAdmin()->assignRole($role1);
        $this->createAdmin()->assignRole($role1);
        $this->createAdmin()->assignRole($role2);
        $this->createAdmin()->assignRole($role2);
        $this->createAdmin()->assignRole($role2);
        $this->createAdmin()->assignRole($role3);
        $this->createAdmin()->assignRole($role3);
        $this->createAdmin()->assignRole($role3);
        $this->createAdmin()->assignRole($role3);

        $admins = $this->requestToAdminListRoute(['roles' => [$role1->id, $role2->id]])
            ->assertOk()
            ->json('data');
        self::assertCount(5, $admins);

        $admins = $this->requestToAdminListRoute(['roles' => [$role1->id]])
            ->assertOk()
            ->json('data');
        self::assertCount(2, $admins);

        $admins = $this->requestToAdminListRoute(['roles' => [$role3->id]])
            ->assertOk()
            ->json('data');
        self::assertCount(4, $admins);
    }
}
