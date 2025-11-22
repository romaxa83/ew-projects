<?php

namespace Tests\Feature\Mutations\Admin\Admin;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class RestoreTest extends TestCase
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
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_RESTORE])
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')
            ->softDeleted()->create();

        $this->assertTrue($someAdmin->trashed());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($someAdmin->id)])
            ->assertOk();

        $responseData = $response->json('data.adminRestore');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($responseData['id'], $someAdmin->id);

        $someAdmin->refresh();

        $this->assertFalse($someAdmin->trashed());
        $this->assertTrue($someAdmin->isActive());
    }

    /** @test */
    public function fail_not_found_user()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_RESTORE])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr(99)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('error.not found model'));
    }


    /** @test */
    public function fail_not_trashed_user()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_RESTORE])
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $this->assertFalse($someAdmin->trashed());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($someAdmin->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('error.model not trashed'));
    }

    /** @test */
    public function fail_not_auth()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_RESTORE])
            ->create();

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $this->assertFalse($someAdmin->trashed());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($someAdmin->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('auth.not auth'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function fail_not_perm()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::ADMIN_EDIT])
            ->create();
        $this->loginAsAdmin($admin);

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $this->assertFalse($someAdmin->trashed());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($someAdmin->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('auth.not perm'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                adminRestore(id: %s) {
                    id
                    status
                }
            }',
            $id,
        );
    }
}




