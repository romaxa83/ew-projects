<?php

namespace Tests\Feature\Http\Api\V1\User;

use App\Exceptions\ErrorsCode;
use App\Models\User\Car;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\CarBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class VerifyCarFromAATest extends TestCase
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
    public function verify_success()
    {
        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';

        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setUuid($carUuid)
            ->setStatus(Car::MODERATE)
            ->notVerify()
            ->create();

        $car->refresh();

        $this->assertFalse($car->isVerify());
        $this->assertNotEquals($car->inner_status, Car::VERIFY);

        $response = $this->get(
            route('api.v1.user.car.verify',[
                'carId' => $carUuid,
            ]),
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $car->refresh();

        $this->assertTrue($car->isVerify());
        $this->assertEquals($car->inner_status, Car::VERIFY);
    }

    /** @test */
    public function not_found_car()
    {
        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';

        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setStatus(Car::MODERATE)
            ->notVerify()
            ->create();

        $car->refresh();

        $this->assertFalse($car->isVerify());
        $this->assertNotEquals($car->inner_status, Car::VERIFY);

        $response = $this->get(
            route('api.v1.user.car.verify',[
                'carId' => $carUuid,
            ]),
            $this->headers()
        );

        $this->assertEquals($response->status(), 400);
        $this->assertFalse($response->json('success'));
        $this->assertEquals($response->json('data'), "Not found car by [carId - {$carUuid}]");
    }

    /** @test */
    public function not_auth()
    {
        $carUuid = '45b0d8f2-f4f6-11eb-8274-4cd98fc26f15';

        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setUuid($carUuid)
            ->setStatus(Car::MODERATE)
            ->notVerify()
            ->create();

        $car->refresh();

        $this->assertFalse($car->isVerify());
        $this->assertNotEquals($car->inner_status, Car::VERIFY);

        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $response = $this->get(
            route('api.v1.user.car.verify',[
                'carId' => $carUuid,
            ]),
            $headers
        )->assertStatus(ErrorsCode::NOT_AUTH)
        ;

        $this->assertEquals($response->json('data'), 'Bad authorization token');
        $this->assertFalse($response->json('success'));
    }
}



