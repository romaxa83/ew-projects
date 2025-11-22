<?php

namespace Tests\Unit\Services\AA\Commands;

use App\Events\User\SaveCarFromAA;
use App\Events\User\SendCarDataToAA;
use App\Helpers\ConvertNumber;
use App\Listeners\User\AttachLoyaltyListeners;
use App\Models\AA\AAResponse;
use App\Models\User\Car;
use App\Models\User\OrderCar\OrderCarStatus;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Commands\GetCar;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\User\CarService;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class GetCarTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;

    /** @test */
    public function success()
    {
        // создаем пользователя
        $user = $this->userBuilder()->create();
        $user->refresh();

        $response = self::successData();
        $car = $this->carBuilder()
            ->setUserId($user->id)
            ->setVin($response['data']['vin'])
            ->setNumber($response['data']['number'])
            ->create()
        ;
        $car->refresh();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('getRequest')->willReturn($response);

        $this->assertEmpty($user->aaResponses);
        $this->assertNull($car->uuid);
        $this->assertEmpty($car->carOrder);
        $this->assertEmpty($car->confidants);

        $model = (new GetCar(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
            resolve(CarService::class),
        ))->handler($car);


        $this->assertTrue($model instanceof Car);
        $this->assertEquals($model->id, $car->id);

        $user->refresh();
        $car->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertEquals($user->aaResponses[0]->type, AAResponse::TYPE_GET_CAR);
        $this->assertEquals($user->aaResponses[0]->status, AAResponse::STATUS_SUCCESS);
        $this->assertNotNull($car->uuid);
        $this->assertEquals($car->uuid, Arr::get($response, 'data.id'));

        // проверяем машины
        $this->assertEquals($car->year, Arr::get($response, 'data.year'));
        $this->assertEquals($car->vin, Arr::get($response, 'data.vin'));
        $this->assertEquals($car->owner_uuid, Arr::get($response, 'data.owner'));
        $this->assertEquals($car->is_personal, Arr::get($response, 'data.personal'));
        $this->assertEquals($car->is_buy, Arr::get($response, 'data.buy'));
        $this->assertEquals($car->inner_status, Car::VERIFY);
        $this->assertTrue($car->is_verify);
        $this->assertTrue($car->is_moderate);
        $this->assertTrue($car->is_order);

        // проверяем привязку купонов
        $this->assertEquals($car->year_deal, Arr::get($response, 'data.yearDeal'));
        $this->assertNotEmpty($car->loyalties);

        // данные по заказу авто
        $this->assertNotNull($car->carOrder);
        $this->assertEquals($car->carOrder->payment_status, Arr::get($response, 'data.orderCar.paymentStatusCar'));
        $this->assertEquals($car->carOrder->order_number, Arr::get($response, 'data.orderCar.orderNumber'));
        $this->assertEquals(ConvertNumber::fromNumberToFloat($car->carOrder->sum), Arr::get($response, 'data.orderCar.sum'));
        $this->assertEquals(ConvertNumber::fromNumberToFloat($car->carOrder->sum_discount), Arr::get($response, 'data.orderCar.sumDiscount'));
        $this->assertCount(OrderCarStatus::count(), $car->carOrder->statuses);

        // конф. лица
        $this->assertNotEmpty($car->confidants);
        $this->assertEquals($car->confidants[0]->uuid, Arr::get($response, 'data.proxies.0.id'));
        $this->assertEquals($car->confidants[0]->name, Arr::get($response, 'data.proxies.0.name'));
        $this->assertEquals($car->confidants[0]->phone, Arr::get($response, 'data.proxies.0.number'));
    }

    /** @test */
    public function success_without_year_deal()
    {
        \Event::fake([
            SaveCarFromAA::class
        ]);

        // создаем пользователя
        $user = $this->userBuilder()->create();
        $user->refresh();

        $response = self::successData();
        $response['data']['yearDeal'] = "";

        $car = $this->carBuilder()
            ->setUserId($user->id)
            ->setVin($response['data']['vin'])
            ->setNumber($response['data']['number'])
            ->create()
        ;
        $car->refresh();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('getRequest')->willReturn($response);

        $this->assertEmpty($user->aaResponses);
        $this->assertNull($car->uuid);
        $this->assertEmpty($car->loyalties);

        (new GetCar(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
            resolve(CarService::class),
        ))->handler($car);

        $car->refresh();
        $user->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertNotNull($car->uuid);

        // проверяем привязку купонов
        $this->assertNull($car->year_deal);
        $this->assertEmpty($car->loyalties);

        \Event::assertDispatched(SaveCarFromAA::class);
        \Event::assertListening(SaveCarFromAA::class, AttachLoyaltyListeners::class);

    }

    /** @test */
    public function success_without_order()
    {
        // создаем пользователя
        $user = $this->userBuilder()->create();
        $user->refresh();

        $response = self::successData();
        $response['data']['orderCar'] = null;

        $car = $this->carBuilder()
            ->setUserId($user->id)
            ->setVin($response['data']['vin'])
            ->setNumber($response['data']['number'])
            ->create()
        ;
        $car->refresh();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('getRequest')->willReturn($response);

        $this->assertEmpty($user->aaResponses);
        $this->assertNull($car->uuid);
        $this->assertEmpty($car->carOrder);

        (new GetCar(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
            resolve(CarService::class),
        ))->handler($car);

        $car->refresh();
        $user->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertNotNull($car->uuid);
        $this->assertEmpty($car->carOrder);
    }

    /** @test */
    public function success_without_confidants()
    {
        // создаем пользователя
        $user = $this->userBuilder()->create();
        $user->refresh();

        $response = self::successData();
        $response['data']['proxies'] = null;

        $car = $this->carBuilder()
            ->setUserId($user->id)
            ->setVin($response['data']['vin'])
            ->setNumber($response['data']['number'])
            ->create()
        ;
        $car->refresh();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('getRequest')->willReturn($response);

        $this->assertEmpty($user->aaResponses);
        $this->assertNull($car->uuid);
        $this->assertEmpty($car->confidants);

        (new GetCar(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
            resolve(CarService::class),
        ))->handler($car);

        $car->refresh();
        $user->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertNotNull($car->uuid);
        $this->assertEmpty($car->confidants);
    }

    /** @test */
    public function not_user_in_aa_service()
    {
        // создаем пользователя
        $user = $this->userBuilder()->create();
        $user->refresh();

        $response = self::successData();
        $response['data']['proxies'] = null;

        $car = $this->carBuilder()
            ->setUserId($user->id)
            ->setVin($response['data']['vin'])
            ->setNumber($response['data']['number'])
            ->create()
        ;
        $car->refresh();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('getRequest')->willThrowException(new AARequestException());

        \Event::fake([SendCarDataToAA::class]);

        $this->assertEmpty($user->aaResponses);

        (new GetCar(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
            resolve(CarService::class),
        ))->handler($car);

        $user->refresh();
        $car->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertEquals($user->aaResponses[0]->status, AAResponse::STATUS_ERROR);
        $this->assertNull($car->uuid);

    }

    public static function successData(): array
    {
        return [
            "success" => true,
            "data" => [
                "id" => "a2fac0f7-f4f8-11eb-8274-4cd98fc26f15",
                "name" => "Logan Сірий № AI4921CK VI",
                "brand" => "32a075c1-45e6-11e7-a80b-005056812a5f",
                "model" => "c49359d9-de58-11eb-837f-000c29f1d0a8",
                "year" => "2010",
                "yearDeal" => "2015",
                "vin" => "VF1KSRAB5440179",
                "number" => "7897897",
                "owner" => "35b0d8f2-f4f6-11eb-8274-4cd98fc26f15",
                "statusCar" => true,
                "personal" => true,
                "buy" => true,
                "orderCar" => [
                    "orderNumber" => "245",
                    "paymentStatusCar" => 2,
                    "sumDiscount" => 1000,
                    "sum" => 150000,
                ],
                "proxies" => [
                    [
                        "id" => "97ed860e-f4f4-11eb-8274-4cd98fc26f15",
                        "name" => "Кравчук Олег Петрович",
                        "number" => "0968381637",
                        "codeOKPO" => "",
                        "email" => "Нет",
                        "verified" => true
                    ]
                ],
                "verified" => true
            ],
            "message" => ""
        ];
    }
}


