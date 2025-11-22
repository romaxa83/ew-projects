<?php

namespace Tests\Feature\Mutations\Permission;

use App\Models\Admin\Admin;
use App\Models\Permission\Permission;
use App\Models\Permission\Role;
use App\Types\Permissions as Type;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class AttachPermissionsTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function attach_success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Type::PERMISSION_ATTACH)
            ->create();
        $this->loginAsAdmin($admin);

        $limit = 5;
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        $perms = Permission::query()->limit($limit)->get()->pluck('name')->toArray();

        $this->assertFalse($role->hasAllPermissions($perms));

        $query = sprintf('
            mutation {
                permissionsAttach(input:{
                    id:"%s",
                    permissions:["%s", "%s", "%s", "%s", "%s"]
                }) {
                    id
                    name
                    permissions {
                        name
                    }
                }
            }',
            $role->id,
            $perms[0],
            $perms[1],
            $perms[2],
            $perms[3],
            $perms[4]
        );

        $response = $this->postGraphQL(['query' => $query])
            ->assertOk();

        $role->refresh();
        $this->assertTrue($role->hasAllPermissions($perms));

        $responseData = $response->json('data.permissionsAttach');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('permissions', $responseData);
        $this->assertCount($limit, $responseData['permissions']);
        $this->assertArrayHasKey('name', $responseData['permissions'][0]);
        $this->assertEquals($responseData['permissions'][0]['name'], $perms[0]);
        $this->assertEquals($responseData['id'], $role->id);
    }

    /** @test */
    public function remove_all_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Type::PERMISSION_ATTACH)
            ->create();
        $this->loginAsAdmin($admin);

        $limit = 5;
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        $perms = Permission::query()->limit($limit)->get()->pluck('name')->toArray();

        $role->syncPermissions($perms);

        $this->assertTrue($role->hasAllPermissions($perms));
        $this->assertCount($limit, $role->permissions);

        $query = sprintf('
            mutation {
                permissionsAttach(input:{
                    id:"%s",
                    permissions:[]
                }) {
                    id
                    name
                    permissions {
                        name
                    }
                }
            }',
            $role->id
        );

        $response = $this->postGraphQL(['query' => $query])
            ->assertOk();

        $role->refresh();

        $responseData = $response->json('data.permissionsAttach');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('permissions', $responseData);
        $this->assertEmpty($responseData['permissions']);
        $this->assertCount(0, $responseData['permissions']);
    }

    /** @test */
    public function remove_some_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Type::PERMISSION_ATTACH)
            ->create();
        $this->loginAsAdmin($admin);

        $limit = 3;
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        $perms = Permission::query()->limit($limit)->get()->pluck('name')->toArray();

        $role->syncPermissions($perms);

        $this->assertTrue($role->hasAllPermissions($perms));
        $this->assertCount($limit, $role->permissions);

        $query = sprintf('
            mutation {
                permissionsAttach(input:{
                    id:"%s",
                    permissions:["permission.attach"]
                }) {
                    id
                    name
                    permissions {
                        name
                    }
                }
            }',
            $role->id
        );

        $response = $this->postGraphQL(['query' => $query])
            ->assertOk();

        $role->refresh();

        $responseData = $response->json('data.permissionsAttach');

        $this->assertCount(1, $responseData['permissions']);
    }

    /** @test */
    public function attach_more_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Type::PERMISSION_ATTACH)
            ->create();
        $this->loginAsAdmin($admin);

        $limit = 3;
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        $perms = Permission::query()->limit($limit)->get()->pluck('name')->toArray();

        $role->syncPermissions($perms);

        $this->assertTrue($role->hasAllPermissions($perms));
        $this->assertCount($limit, $role->permissions);

        $perms[] = Type::PERMISSION_ATTACH;

        $query = sprintf('
            mutation {
                permissionsAttach(input:{
                    id:"%s",
                    permissions:["%s", "%s", "%s", "%s"]
                }) {
                    id
                    name
                    permissions {
                        name
                    }
                }
            }',
            $role->id,
            $perms[0],
            $perms[1],
            $perms[2],
            $perms[3]
        );

        $response = $this->postGraphQL(['query' => $query])
            ->assertOk();

        $role->refresh();

        $responseData = $response->json('data.permissionsAttach');

        $this->assertCount(4, $responseData['permissions']);
    }
}
