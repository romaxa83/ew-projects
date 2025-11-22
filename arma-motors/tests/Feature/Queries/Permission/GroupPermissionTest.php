<?php

namespace Tests\Feature\Queries\Permission;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions as Type;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class GroupPermissionTest extends TestCase
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
        $admin = $this->adminBuilder()->createRoleWithPerm(Type::PERMISSION_LIST)->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryAllStr())
            ->assertOk();

        $responseData = $response->json('data.permissionGroups');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]);
        $this->assertArrayHasKey('sort', $responseData[0]);
        $this->assertArrayHasKey('current', $responseData[0]);
        $this->assertArrayHasKey('translations', $responseData[0]);
        $this->assertArrayHasKey('permissions', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]['permissions'][0]);
        $this->assertArrayHasKey('name', $responseData[0]['translations'][0]);
        $this->assertArrayHasKey('lang', $responseData[0]['translations'][0]);
        $this->assertArrayHasKey('lang', $responseData[0]['current']);
        $this->assertArrayHasKey('name', $responseData[0]['current']);
        $this->assertEquals(\App::getLocale(), $responseData[0]['current']['lang']);
    }

    /** @test */
    public function list_sort()
    {
        $admin = $this->adminBuilder()->createRoleWithPerm(Type::PERMISSION_LIST)->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryAllStr())
            ->assertOk();

        $firstElement = $response->json('data.permissionGroups.0.sort');

        $response = $this->graphQL($this->getQueryAllStr('DESC'))
            ->assertOk();

        $this->assertNotEquals($firstElement, $response->json('data.permissionGroups.0.sort'));
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

    private function getQueryAllStr($sort = 'ASC'): string
    {
        return sprintf('{
            permissionGroups (orderBy: [{ field: SORT, order: %s }]) {
                id
                name
                sort
                current {
                    name
                    lang
                }
                translations {
                    name
                    lang
                }
                permissions {
                    name
                }
            }
        }',
        $sort
        );
    }
}
