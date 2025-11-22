<?php

namespace Tests\Unit\Services\AA\Commands;

use App\Models\AA\AAResponse;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\User\Car;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Commands\CreateCar;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\User\CarService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class CreateCarTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;

    /** @test */
    public function success()
    {
        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();
        $model = Model::orderBy(\DB::raw('RAND()'))->first();

        $response = self::successData();
        // создаем пользователя
        $user = $this->userBuilder()->setUuid($response['data']['owner'])->create();
        $user->refresh();

        $car = $this->carBuilder()
            ->notVerify()
            ->setUserId($user->id)
            ->setModelId($model->id)
            ->setBrandId($brand->id)
            ->setVin($response['data']['vin'])
            ->setNumber($response['data']['number'])
            ->create()
        ;
        $car->refresh();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertEmpty($user->aaResponses);
        $this->assertNull($car->uuid);
        $this->assertFalse($car->is_verify);
        $this->assertNotEquals($car->inner_status, Car::VERIFY);

        (new CreateCar(
            $sender,
            resolve(ResponseService::class),
            resolve(CarService::class),
        ))->handler($car);

        $user->refresh();
        $car->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertNotEmpty($user->aaResponses[0]->status, AAResponse::STATUS_SUCCESS);
        $this->assertNotEmpty($user->aaResponses[0]->type, AAResponse::TYPE_CREATE_CAR);
        $this->assertNotNull($car->uuid);
        $this->assertTrue($car->is_verify);
        $this->assertEquals($car->inner_status, Car::VERIFY);
    }

    /** @test */
    public function something_wrong()
    {
        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();
        $model = Model::orderBy(\DB::raw('RAND()'))->first();

        $response = self::successData();
        // создаем пользователя
        $user = $this->userBuilder()->setUuid($response['data']['owner'])->create();
        $user->refresh();

        $car = $this->carBuilder()
            ->notVerify()
            ->setUserId($user->id)
            ->setModelId($model->id)
            ->setBrandId($brand->id)
            ->setVin($response['data']['vin'])
            ->setNumber($response['data']['number'])
            ->create()
        ;
        $car->refresh();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willThrowException(new AARequestException());

        $this->assertEmpty($user->aaResponses);

        (new CreateCar(
            $sender,
            resolve(ResponseService::class),
            resolve(CarService::class),
        ))->handler($car);

        $user->refresh();
        $car->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertEquals($user->aaResponses[0]->status, AAResponse::STATUS_ERROR);
    }

    public static function successData(): array
    {
        return [
            "success" => true,
            "data" => [
                "vin" => "1111118899",
                "number" => "AA3332AA",
                "year" => 2010,
                "model" => "407537f6-de58-11eb-837f-000c29f1d0a8",
                "id" => "a2b2f255-fc0a-11eb-8274-4cd98fc26f15",
                "name" => "",
                "brand" => "7e127712-45b4-11e7-a80b-005056812a5f",
                "owner" => "112a1806-fa73-11eb-8274-4cd98fc26f15",
                "yearDeal" => "",
                "personal" => true,
            ],
            "message" => ""
        ];
    }
}
