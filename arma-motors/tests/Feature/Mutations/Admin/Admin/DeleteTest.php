<?php

namespace Tests\Feature\Mutations\Admin\Admin;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Queries\Admin\GetListAdminTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        Admin::factory()->count(5)->create();

        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_DELETE, Permissions::ADMIN_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')
            ->setStatus(Admin::STATUS_INACTIVE)->create();

        $this->assertNull($someAdmin->deleted_at);
        // запрос на просмотр всех админов
        $responseList = $this->postGraphQL(['query' => GetListAdminTest::getQueryStr()]);
        $this->assertEquals(7, $responseList->json('data.admins.paginatorInfo.count'));

        $response = $this->postGraphQL(['query' => $this->getQueryStr($someAdmin->id)])
            ->assertOk();

        $responseData = $response->json('data.adminDelete');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.admin deleted'));

        $someAdmin->refresh();

        $this->assertNotNull($someAdmin->deleted_at);

        // дополнительный запрос на просмотр всех админов, должно быть на одного меньше
        $responseList = $this->postGraphQL(['query' => GetListAdminTest::getQueryStr()]);
        $this->assertEquals(6, $responseList->json('data.admins.paginatorInfo.count'));
    }

    /** @test */
    public function fail_not_deactivate_admin()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_DELETE])
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $this->assertNull($someAdmin->deleted_at);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($someAdmin->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('error.must deactivate model before delete'));

        $someAdmin->refresh();

        $this->assertNull($someAdmin->deleted_at);
    }

    /** @test */
    public function fail_not_found_admin()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_DELETE])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr(99)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('error.not found model'));
    }

    /** @test */
    public function fail_not_auth()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_DELETE])
            ->create();

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($someAdmin->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function fail_not_perm()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($someAdmin->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                adminDelete(id: %s) {
                    status
                    message
                }
            }',
            $id,
        );
    }
}



