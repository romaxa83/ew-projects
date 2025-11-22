<?php

namespace Tests\Feature\Http\Api\V1\User;

use App\Exceptions\ErrorsCode;
use App\Helpers\ConvertNumber;
use App\Models\Catalogs\Car\Model;
use App\Models\User\Car;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\CarBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;
use Illuminate\Http\Response;

class UserCarEditFromAATest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;
    use CarBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->armaAuth();
    }

    public function headers()
    {
        return [
            'Authorization' => 'Basic d2V6b20tYXBpOndlem9tLWFwaQ=='
        ];
    }

    /** @test */
    public function change_success()
    {
        $model = Model::where('name','S60')->first();

        $data = $this->data();
        $data['model'] = $model->uuid->getValue();
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';

        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $this->loginAsUser($user);
        $user->refresh();

        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setUuid($carUuid)
            ->setStatus(Car::MODERATE)
            ->notVerify()
            ->withoutVin()
            ->setUserId($user->id)
            ->withOrder()
            ->create();

        $car->refresh();

        $this->assertNotNull($car->carOrder);
        $this->assertEmpty($car->confidants);
        $this->assertNull($car->vin);
        $this->assertNull($car->name_aa);
        $this->assertFalse($car->isVerify());
        $this->assertEquals($car->inner_status, Car::MODERATE);
        $this->assertNotEquals($car->number, $data['number']);
        $this->assertNotEquals($car->year, $data['year']);
        $this->assertNotEquals($car->model_id, $model->id);
        $this->assertNotEquals($car->carOrder->payment_status, $data['orderCar']['statusPayment']);
        $this->assertNotEquals($car->carOrder->sum, $data['orderCar']['sum']);
        $this->assertNotEquals($car->carOrder->sum_discount, $data['orderCar']['sumDiscount']);

        $response = $this->post(
            route('api.v1.user.car.edit',[
                'userId' => $userUuid,
                'carId' => $carUuid,
            ]),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $car->refresh();

        $this->assertEquals($car->number->getValue(), $data['number']);
        $this->assertEquals($car->vin->getValue(), $data['vin']);
        $this->assertTrue($car->isVerify());
        $this->assertEquals($car->inner_status, Car::VERIFY);
        $this->assertEquals($car->name_aa, $data['name']);
        $this->assertEquals($car->year, $data['year']);
        $this->assertEquals($car->model_id, $model->id);

        $this->assertEquals($car->carOrder->payment_status, $data['orderCar']['statusPayment']);
        $this->assertEquals(ConvertNumber::fromNumberToFloat($car->carOrder->sum), $data['orderCar']['sum']);
        $this->assertEquals(ConvertNumber::fromNumberToFloat($car->carOrder->sum_discount), $data['orderCar']['sumDiscount']);

        $this->assertNotEmpty($car->confidants);
        $this->assertCount(count($data['proxies']), $car->confidants);
    }

    /** @test */
    public function not_found_model()
    {
        $data = $this->data();
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';

        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $this->loginAsUser($user);
        $user->refresh();

        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setUuid($carUuid)
            ->setStatus(Car::MODERATE)
            ->notVerify()
            ->withoutVin()
            ->setUserId($user->id)
            ->withOrder()
            ->create();

        $car->refresh();

        $response = $this->post(
            route('api.v1.user.car.edit',[
                'userId' => $userUuid,
                'carId' => $carUuid,
            ]),
            $data,
            $this->headers()
        )->assertStatus(Response::HTTP_NOT_FOUND);

        $this->assertFalse($response->json('success'));
        $this->assertEquals($response->json('data'), __('error.not found model'));
    }

    /** @test */
    public function change_verify()
    {
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';

        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $this->loginAsUser($user);
        $user->refresh();

        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setUuid($carUuid)
            ->setStatus(Car::VERIFY)
            ->withoutVin()
            ->setUserId($user->id)
            ->withOrder()
            ->create();

        $car->refresh();

        $data = [
            'verify' => false,
        ];

        $this->assertTrue($car->isVerify());
        $this->assertEquals($car->inner_status, Car::VERIFY);

        $response = $this->post(
            route('api.v1.user.car.edit',[
                'userId' => $userUuid,
                'carId' => $carUuid,
            ]),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $car->refresh();

        $this->assertFalse($car->isVerify());
        $this->assertEquals($car->inner_status, Car::MODERATE);
    }

    /** @test */
    public function not_found_user()
    {
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';

        $user = $this->userBuilder()->setUuid('35b0d8f2-f4f6-11eb-8274-4cd98fc26f14')->create();
        $this->loginAsUser($user);
        $user->refresh();

        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setUuid($carUuid)
            ->withoutVin()
            ->setUserId($user->id)
            ->create();

        $car->refresh();

        $data = [
            'number' => '9999999',
            'vin' => '8685111',
            'verify' => true,
        ];

        $response = $this->post(
            route('api.v1.user.car.edit',[
                'userId' => $userUuid,
                'carId' => $carUuid,
            ]),
            $data,
            $this->headers()
        )
        ;

        $this->assertEquals($response->status(), 400);
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function not_found_car()
    {
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';

        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $this->loginAsUser($user);
        $user->refresh();

        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setUuid('45b0d8f2-f4f6-11eb-8274-4cd98fc26f14')
            ->withoutVin()
            ->setUserId($user->id)
            ->create();

        $car->refresh();

        $data = [
            'number' => '9999999',
            'vin' => '8685111',
            'verify' => true,
        ];

        $response = $this->post(
            route('api.v1.user.car.edit',[
                'userId' => $userUuid,
                'carId' => $carUuid,
            ]),
            $data,
            $this->headers()
        )
        ;

        $this->assertEquals($response->status(), 400);
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function not_required_field()
    {
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';

        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $this->loginAsUser($user);
        $user->refresh();

        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setUuid('45b0d8f2-f4f6-11eb-8274-4cd98fc26f14')
            ->withoutVin()
            ->setUserId($user->id)
            ->create();

        $car->refresh();

        $data = [
            'number' => '9999999',
            'vin' => '8685111',
        ];

        $response = $this->post(
            route('api.v1.user.car.edit',[
                'userId' => $userUuid,
                'carId' => $carUuid,
            ]),
            $data,
            $this->headers()
        )
        ;

        $this->assertEquals($response->status(), 400);
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function not_auth()
    {
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';

        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $this->loginAsUser($user);
        $user->refresh();

        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setUuid('45b0d8f2-f4f6-11eb-8274-4cd98fc26f14')
            ->withoutVin()
            ->setUserId($user->id)
            ->create();

        $car->refresh();

        $data = [
            'number' => '9999999',
            'vin' => '8685111',
        ];

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $response = $this->post(
            route('api.v1.user.car.edit',[
                'userId' => $userUuid,
                'carId' => $carUuid,
            ]),
            $data,
            $headers
        )->assertStatus(ErrorsCode::NOT_AUTH)
        ;

        $this->assertEquals($response->json('data'), 'Bad authorization token');
        $this->assertFalse($response->json('success'));
    }

    private function data(): array
    {
        return [
            'number' => '9999999',
            'vin' => '8685111',
            'verify' => true,
            'name' => "Octavia",
            'year' => 1990,
            'model' => '35b0d8f2-f4f6-11eb-8274-4cd98fc26f13',
            'orderCar' => [
                'statusPayment' => 2,
                'sum' => 5000,
                'sumDiscount' => 5000,
            ],
            'proxies' => [
                [
                    "id" => "97ed860e-f4f4-11eb-8274-4cd98fc26f15",
                    "name" => "rembo3",
                    "number" => "0669898987"
                ],
                [
                    "id" => "97ed860e-f4f4-11eb-8274-4cd98fc26f12",
                    "name" => "rocky",
                    "number" => "0669898987"
                ]
            ]
        ];
    }
}



