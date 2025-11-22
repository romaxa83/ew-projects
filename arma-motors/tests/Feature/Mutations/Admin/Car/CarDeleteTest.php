<?php

namespace Tests\Feature\Mutations\Admin\Car;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class CarDeleteTest extends TestCase
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
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_DELETE])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->softDeleted()->setUserId($user->id)->create();

        $user->refresh();
        $this->assertEmpty($user->cars);
        $this->assertNotEmpty($user->carsTrashed);

        $response = $this->graphQL($this->getQueryStr($car->id))
            ->assertOk();

        $responseData = $response->json('data.carDelete');
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);

        $this->assertEquals(__('message.car deleted'), $responseData['message']);

        $user->refresh();
        $this->assertEmpty($user->cars);
        $this->assertEmpty($user->carsTrashed);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_DELETE])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->softDeleted()->setUserId($user->id)->create();

        $response = $this->graphQL($this->getQueryStr(999));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_DELETE])->create();

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->softDeleted()->setUserId($user->id)->create();

        $response = $this->graphQL($this->getQueryStr($car->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->softDeleted()->setUserId($user->id)->create();

        $response = $this->graphQL($this->getQueryStr($car->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(int $id): string
    {
        return sprintf('
            mutation {
                carDelete(id: "%s") {
                    status
                    message
                }
            }',
            $id
        );
    }
}
