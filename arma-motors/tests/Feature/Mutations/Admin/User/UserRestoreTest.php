<?php

namespace Tests\Feature\Mutations\Admin\User;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class UserRestoreTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use CarBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $builder = $this->adminBuilder();

        $admin = $builder->createRoleWithPerms([Permissions::USER_RESTORE])
            ->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->softDeleted()->create();
        $carBuilder = $this->carBuilder();
        $carBuilder->setUserId($user->id)->softDeleted()->create();
        $carBuilder->setUserId($user->id)->softDeleted()->create();
        $user->refresh();

        $this->assertTrue($user->trashed());
        $this->assertNotEmpty($user->carsTrashed);
        $this->assertCount(2, $user->carsTrashed);
        $this->assertEmpty($user->cars);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($user->id)]);

        $responseData = $response->json('data.adminRestoreUser');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('status', $responseData);

        $this->assertEquals($responseData['id'], $user->id);

        $user->refresh();

        $this->assertFalse($user->trashed());
        $this->assertEmpty($user->carsTrashed);
        $this->assertNotEmpty($user->cars);
        $this->assertCount(2, $user->cars);

    }

    /** @test */
    public function not_found()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::USER_RESTORE])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr(99)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('error.not found model'));
    }

    /** @test */
    public function not_auth()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::USER_RESTORE])
            ->create();

        $user = $this->userBuilder()->softDeleted()->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($user->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::USER_DELETE])
            ->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->softDeleted()->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($user->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                adminRestoreUser(id: %s) {
                    id
                    status
                }
            }',
            $id,
        );
    }
}





