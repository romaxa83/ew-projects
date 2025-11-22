<?php

namespace Tests\Feature\Mutations\Admin\Car;

use App\Exceptions\ErrorsCode;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class CarModerateTest extends TestCase
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
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_MODERATE])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $this->assertTrue($car->isDraft());

        $this->graphQL($this->getQuery($car->id))
            ->assertOk();

        $car->refresh();

        $this->assertFalse($car->isDraft());
        $this->assertTrue($car->isModeration());
    }


    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_MODERATE])->create();

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $response = $this->graphQL($this->getQuery($car->id));

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

        $response = $this->graphQL($this->getQuery($car->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQuery(int $id): string
    {
        return sprintf('
            mutation {
                carModerate(id: "%s") {
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


