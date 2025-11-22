<?php

namespace Tests\Feature\Mutations\Admin\User;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Queries\Admin\GetListAdminTest;
use Tests\Feature\Queries\User\GetListUserTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class UserDeleteTest extends TestCase
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
        $admin = $builder->createRoleWithPerms([Permissions::USER_DELETE, Permissions::USER_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $carBuilder = $this->carBuilder();
        $carBuilder->setUserId($user->id)->create();
        $carBuilder->setUserId($user->id)->create();
        $carBuilder->setUserId($user->id)->create();

        // запрос на просмотр всех пользователей
        $response = $this->postGraphQL(['query' => GetListUserTest::getQueryStr()]);

        foreach ($user->cars as $car){
            $this->assertNull($car->deleted_at);
        }

        $this->assertEquals(1, $response->json('data.users.paginatorInfo.total'));

        // запрос на удаление
        $response = $this->postGraphQL(['query' => $this->getQueryStr($user->id)]);

        $responseData = $response->json('data.adminDeleteUser');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.user deleted'));

        $user->refresh();

        $this->assertNotNull($user->deleted_at);

        foreach ($user->cars as $car){
            $this->assertNotNull($car->deleted_at);
        }

        // доп. запрос на просмотр всех админов, должно быть на одного меньше чем в первом запросе
        $responseList = $this->postGraphQL(['query' => GetListAdminTest::getQueryStr()]);
        $this->assertEquals(0, $responseList->json('data.user.paginatorInfo.count'));

        // @todo проверить заявки на удаление (когда будет реализовано)
    }

    /** @test */
    public function not_found()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerms([Permissions::USER_DELETE])
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
        $admin = $builder->createRoleWithPerms([Permissions::USER_DELETE])
            ->create();

        $someAdmin = $builder->setEmail('some@admin.com')->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($someAdmin->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
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
                adminDeleteUser(id: %s) {
                    status
                    message
                }
            }',
            $id,
        );
    }
}




