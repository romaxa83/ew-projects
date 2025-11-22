<?php

namespace Tests\Feature\Mutations\Admin\Car;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class CarRestoreTest extends TestCase
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
    public function change_success()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_RESTORE])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->softDeleted()->setUserId($user->id)->create();

        $car->refresh();
        $this->assertNotNull($car->deleted_at);

        $response = $this->graphQL($this->getQueryStr($car->id))
            ->assertOk();

        $responseData = $response->json('data.carRestore');
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('vin', $responseData);
        $this->assertArrayHasKey('number', $responseData);
        $this->assertEquals($car->id, $responseData['id']);

        $car->refresh();
        $this->assertNull($car->deleted_at);
    }


    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_RESTORE])->create();

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

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
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $response = $this->graphQL($this->getQueryStr($car->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(int $id): string
    {
        return sprintf('
            mutation {
                carRestore(id: "%s") {
                    id
                    number
                    vin
                    status
                }
            }',
            $id
        );
    }
}



