<?php

namespace Tests\Feature\Queries\Permission;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Models\Permission\Permission;
use App\Models\Permission\Role;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class RoleTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function list_success()
    {
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        $perm = Permission::query()->where('name',Permissions::ROLE_LIST)->first();
        $role->givePermissionTo($perm);
        $admin = $this->adminBuilder()->attachRole($role)->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryAllStr())
            ->assertOk();

        $responseData = $response->json('data.roles');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]);
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryAllStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $response = $this->graphQL($this->getQueryAllStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function one_success()
    {
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        $perm = Permission::query()->where('name',Permissions::ROLE_GET)->first();
        $role->givePermissionTo($perm);
        $admin = $this->adminBuilder()->attachRole($role)->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryOneStr($role->id))
            ->assertOk();

        $responseData = $response->json('data.role');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertEquals($role->id, $responseData['id']);
        $this->assertEquals($role->name, $responseData['name']);
    }

    /** @test */
    public function one_not_perm()
    {
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryOneStr($role->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function one_not_auth()
    {
        $role = Role::factory()->new(['guard_name' => Admin::GUARD])->create();

        $response = $this->graphQL($this->getQueryOneStr($role->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    private function getQueryAllStr(): string
    {
        return sprintf('{
            roles {
                id
                name
               }
            }'
        );
    }

    private function getQueryOneStr($id): string
    {
        return sprintf('{
            role (id: %s) {
                id
                name
               }
            }',
            $id
        );
    }
}

