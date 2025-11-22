<?php

namespace Tests\Feature\Mutations\BackOffice\Permission;

use App\GraphQL\Mutations\BackOffice\Permission\EmployeeRoleSetDefaultForOwnerMutation;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleCreatePermission;
use App\Permissions\Roles\RoleDeletePermission;
use App\Permissions\Roles\RoleListPermission;
use App\Permissions\Roles\RoleUpdatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class EmployeeRoleSetDefaultForOwnerMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const MUTATION = EmployeeRoleSetDefaultForOwnerMutation::NAME;

    public function test_not_permitted_admin_get_unauthorized_error(): void
    {
        $this->loginAsAdmin();
        $this->test_guest_get_unauthorized_error();
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $role = Role::factory()->create(['name' => 'Role 1']);

        $response = $this->query($role->id)
            ->assertOk();

        $this->assertGraphQlUnauthorized($response);
    }

    protected function query(int $roleId): TestResponse
    {
        $query = sprintf(
            'mutation { %s (id: "%s") { message type } }',
            self::MUTATION,
            $roleId
        );

        return $this->postGraphQLBackOffice(compact('query'));
    }

    public function test_it_set_role_for_owner(): void
    {
        $this->loginAsRoleManager();

        $role1 = Role::factory()->create(['name' => 'Role 1']);
        $role2 = Role::factory()->create(['name' => 'Role 2']);

        $this->assertDatabaseHas(
            Role::TABLE,
            [
                'id' => $role1->id,
                'for_owner' => null
            ]
        );
        $this->assertDatabaseHas(
            Role::TABLE,
            [
                'id' => $role2->id,
                'for_owner' => null,
            ]
        );

        $response = $this->query($role2->id)
            ->assertOk();

        [self::MUTATION => $message] = $response->json('data');

        self::assertEquals(
            __('messages.roles.set-as-default-for-owner'),
            $message['message']
        );

        $this->assertDatabaseHas(Role::TABLE, ['id' => $role1->id, 'for_owner' => 0]);
        $this->assertDatabaseHas(Role::TABLE, ['id' => $role2->id, 'for_owner' => 1]);
    }

    protected function loginAsRoleManager(): void
    {
        $this->loginAsAdmin()->assignRole(
            $this->generateRole(
                'User role manager',
                [
                    RoleUpdatePermission::KEY,
                    RoleCreatePermission::KEY,
                    RoleDeletePermission::KEY,
                    RoleListPermission::KEY,
                ],
                Admin::GUARD
            )
        );
    }

    public function test_manager_cant_set_to_default_not_users_role(): void
    {
        $this->loginAsRoleManager();

        $role1 = Role::factory()
            ->admin()
            ->create(['name' => 'Role 1']);

        $this->assertDatabaseHas(
            Role::TABLE,
            [
                'id' => $role1->id,
                'for_owner' => null
            ]
        );

        $response = $this->query($role1->id)
            ->assertOk();

        $this->assertResponseHasValidationMessage($response, 'id', [__('validation.exists', ['attribute' => 'id'])]);
    }

    public function test_it_get_warning_message_for_toggling_already_for_owner_role(): void
    {
        $this->loginAsRoleManager();

        Role::factory()
            ->create(['name' => 'Role 1']);

        $role2 = Role::factory()
            ->asDefault()
            ->create(['name' => 'Role 2']);

        $response = $this->query($role2->id)
            ->assertOk();

        $message = $response->json('data.' . self::MUTATION);

        self::assertEquals(__('messages.roles.cant-be-toggled'), $message['message']);
    }
}
