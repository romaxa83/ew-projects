<?php

namespace Tests\Feature\Mutations\Admin\Car;

use App\Events\User\SendCarDataToAA;
use App\Exceptions\ErrorsCode;
use App\Listeners\User\SendCarDataToAAListeners;
use App\Models\User\Car;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class CarChangeStatusTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use CarBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_EDIT])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $this->assertEquals(Car::DRAFT, $car->inner_status);

        $data = [
            'id' => $car->id,
            'status' => $this->user_car_status_moderate
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $responseData = $response->json('data.carChangeStatus');

        $this->assertEquals($responseData['id'], $data['id']);
        $this->assertEquals($responseData['status'], $data['status']);

        $car->refresh();

        $this->assertEquals(Car::MODERATE, $car->inner_status);
    }

    /** @test */
    public function change_on_moderate_and_send_data_to_aa()
    {
        \Event::fake([
            SendCarDataToAA::class
        ]);

        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_CAR_EDIT])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $user->refresh();

        $this->assertEquals(Car::DRAFT, $car->inner_status);
        $this->assertEmpty($user->aaResponses);

        $data = [
            'id' => $car->id,
            'status' => $this->user_car_status_moderate
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $responseData = $response->json('data.carChangeStatus');

        $this->assertEquals($responseData['id'], $data['id']);
        $this->assertEquals($responseData['status'], $data['status']);

        $car->refresh();
        $user->refresh();

        $this->assertEquals(Car::MODERATE, $car->inner_status);
        $this->assertTrue($car->is_moderate);
        $this->assertTrue($car->isModeration());

         //проверяет запустились ли события
        \Event::assertDispatched(SendCarDataToAA::class);
         //проверяет какие обработчики обработали события
        \Event::assertListening(SendCarDataToAA::class, SendCarDataToAAListeners::class);
    }

    /** @test */
    public function change_on_moderate_and_not_send_data_to_aa_car_have_uuid()
    {
        \Event::fake([
            SendCarDataToAA::class
        ]);

        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_CAR_EDIT])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')->create();
        $car = $this->carBuilder()
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')->setUserId($user->id)->create();

        $data = [
            'id' => $car->id,
            'status' => $this->user_car_status_moderate
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $responseData = $response->json('data.carChangeStatus');

        $this->assertEquals($responseData['id'], $data['id']);
        $this->assertEquals($responseData['status'], $data['status']);

        $car->refresh();
        $user->refresh();

        $this->assertEquals(Car::MODERATE, $car->inner_status);

        //проверяет запустились ли события
        \Event::assertNotDispatched(SendCarDataToAA::class);
    }

    /** @test */
    public function change_on_moderate_and_not_send_data_to_aa_user_not_have_uuid()
    {
        \Event::fake([
            SendCarDataToAA::class
        ]);

        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_CAR_EDIT])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $data = [
            'id' => $car->id,
            'status' => $this->user_car_status_moderate
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $responseData = $response->json('data.carChangeStatus');

        $this->assertEquals($responseData['id'], $data['id']);
        $this->assertEquals($responseData['status'], $data['status']);

        $car->refresh();
        $user->refresh();

        $this->assertEquals(Car::MODERATE, $car->inner_status);

        //проверяет запустились ли события
        \Event::assertNotDispatched(SendCarDataToAA::class);
    }

    /** @test */
    public function change_status_to_verify()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_EDIT])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $data = [
            'id' => $car->id,
            'status' => $this->user_car_status_verify
        ];

        $response = $this->graphQL($this->getQuery($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.can\'t change to verify status'), $response->json('errors.0.message'));
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_EDIT])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $this->assertEquals(Car::DRAFT, $car->inner_status);

        $data = [
            'id' => 999,
            'status' => $this->user_car_status_moderate
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function wrong_status()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_EDIT])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $this->assertEquals(Car::DRAFT, $car->inner_status);

        $data = [
            'id' => $car->id,
            'status' => 'error'
        ];

        $response = $this->graphQL($this->getQuery($data))
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::USER_CAR_EDIT])->create();

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $data = [
            'id' => $car->id,
            'status' => $this->user_car_status_moderate
        ];

        $response = $this->graphQL($this->getQuery($data));

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

        $data = [
            'id' => $car->id,
            'status' => $this->user_car_status_moderate
        ];

        $response = $this->graphQL($this->getQuery($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQuery(array $data): string
    {
        return sprintf('
            mutation {
                carChangeStatus(input: {
                    id: "%s"
                    status: %s
                }) {
                    id
                    number
                    vin
                    status
                }
            }',
            $data['id'],
            $data['status']
        );
    }
}
