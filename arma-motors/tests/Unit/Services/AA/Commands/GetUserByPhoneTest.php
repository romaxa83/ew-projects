<?php

namespace Tests\Unit\Services\AA\Commands;

use App\Events\User\NotUserFromAA;
use App\Events\User\SaveCarFromAA;
use App\Helpers\ConvertNumber;
use App\Models\AA\AAResponse;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\User\Car;
use App\Models\User\OrderCar\OrderCarStatus;
use App\Models\User\User;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Commands\GetUserByPhone;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\User\CarService;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class GetUserByPhoneTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    /** @test */
    public function success()
    {
        // создаем пользователя
        $user = $this->userBuilder()->setPhone('380443311080')->create();
        $user->refresh();

        // эмулируем запрос к AA
        $response = self::successData();
        $sender = $this->createStub(RequestClient::class);
        $sender->method('getRequest')->willReturn($response);

        $this->assertEmpty($user->aaResponses);
        $this->assertEmpty($user->cars);
        $this->assertNull($user->uuid);
        $this->assertNull($user->egrpoy);

        $model = (new GetUserByPhone(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
            resolve(CarService::class),
        ))->handler($user);

        $this->assertTrue($model instanceof User);
        $this->assertEquals($model->id, $user->id);

        $user->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertNotNull($user->uuid);
        $this->assertEquals($user->uuid, Arr::get($response, 'data.user.id'));
        $this->assertNotNull($user->egrpoy);
        $this->assertEquals($user->egrpoy, Arr::get($response, 'data.user.codeOKPO'));

        // проверяем машины
        $this->assertNotEmpty($user->cars);
        $this->assertCount(3, $user->cars);

        $brand = Brand::query()->where('uuid', Arr::get($response, 'data.vechilces.0.brand'))->first();
        $modelCar = Model::query()->where('uuid', Arr::get($response, 'data.vechilces.0.model'))->first();

        $this->assertEquals($user->cars[0]->uuid, Arr::get($response, 'data.vechilces.0.id'));
        $this->assertEquals($user->cars[0]->brand_id, $brand->id);
        $this->assertEquals($user->cars[0]->model_id, $modelCar->id);
        $this->assertEquals($user->cars[0]->year, Arr::get($response, 'data.vechilces.0.year'));
        $this->assertEquals($user->cars[0]->vin, Arr::get($response, 'data.vechilces.0.vin'));
        $this->assertEquals($user->cars[0]->number, Arr::get($response, 'data.vechilces.0.number'));
        $this->assertEquals($user->cars[0]->owner_uuid, Arr::get($response, 'data.vechilces.0.owner'));
        $this->assertEquals($user->cars[0]->owner_uuid, Arr::get($response, 'data.vechilces.0.owner'));
        $this->assertEquals($user->cars[0]->is_personal, Arr::get($response, 'data.vechilces.0.personal'));
        $this->assertEquals($user->cars[0]->is_buy, Arr::get($response, 'data.vechilces.0.buy'));
        $this->assertEquals($user->cars[0]->inner_status, Car::VERIFY);
        $this->assertFalse($user->cars[0]->is_add_to_app);
        $this->assertFalse($user->cars[0]->in_garage);
        $this->assertTrue($user->cars[0]->is_verify);
        $this->assertTrue($user->cars[0]->is_moderate);
        $this->assertTrue($user->cars[0]->is_order);
        $this->assertFalse($user->cars[1]->is_order);

        $this->assertFalse($user->cars[0]->selected);
        $this->assertTrue($user->cars[1]->selected);

        // проверяем привязку купонов
        $this->assertEquals($user->cars[0]->year_deal, Arr::get($response, 'data.vechilces.0.yearDeal'));
        $this->assertNotEmpty($user->cars[0]->loyalties);
        $this->assertCount(3, $user->cars[0]->loyalties);

        $this->assertNull($user->cars[1]->number);
        $this->assertNull($user->cars[1]->year_deal);
        $this->assertEmpty($user->cars[1]->loyalties);

        // данные по заказу авто
        $this->assertNull($user->cars[1]->carOrder);
        $this->assertNotNull($user->cars[0]->carOrder);
        $this->assertEquals($user->cars[0]->carOrder->payment_status, Arr::get($response, 'data.vechilces.0.orderCar.paymentStatusCar'));
        $this->assertEquals($user->cars[0]->carOrder->order_number, Arr::get($response, 'data.vechilces.0.orderCar.orderNumber'));
        $this->assertEquals(ConvertNumber::fromNumberToFloat($user->cars[0]->carOrder->sum), Arr::get($response, 'data.vechilces.0.orderCar.sum'));
        $this->assertEquals(ConvertNumber::fromNumberToFloat($user->cars[0]->carOrder->sum_discount), Arr::get($response, 'data.vechilces.0.orderCar.sumDiscount'));
        $this->assertCount(OrderCarStatus::count(), $user->cars[0]->carOrder->statuses);

        // конф. лица
        $this->assertNotEmpty($user->cars[0]->confidants);
        $this->assertEquals($user->cars[0]->confidants[0]->uuid, Arr::get($response, 'data.vechilces.0.proxies.0.id'));
        $this->assertEquals($user->cars[0]->confidants[0]->name, Arr::get($response, 'data.vechilces.0.proxies.0.name'));
        $this->assertEquals($user->cars[0]->confidants[0]->phone, Arr::get($response, 'data.vechilces.0.proxies.0.number'));
        $this->assertEmpty($user->cars[1]->confidants);
    }

    /** @test */
    public function has_some_error()
    {
        // создаем пользователя
        $user = $this->userBuilder()->setPhone('380443311080')->create();
        $user->refresh();

        $this->assertEmpty($user->aaResponses);

        // эмулируем запрос к AA
        $response = self::successData();
        $response['data']['vechilces'][0]['brand'] = '32a075c1-45e6-11e7-a80b-000000000001';
        $sender = $this->createStub(RequestClient::class);
        $sender->method('getRequest')->willReturn($response);

        $this->expectException(AARequestException::class);

        (new GetUserByPhone(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
            resolve(CarService::class),
        ))->handler($user);
    }

    /** @test */
    public function check_events()
    {
        // создаем пользователя
        $user = $this->userBuilder()->setPhone('380443311080')->create();
        $user->refresh();

        $this->assertEmpty($user->aaResponses);

        \Event::fake([SaveCarFromAA::class]);

        // эмулируем запрос к AA
        $response = self::successData();
        $response['data']['vechilces'][0]['brand'] = '32a075c1-45e6-11e7-a80b-000000000001';
        $sender = $this->createStub(RequestClient::class);
        $sender->method('getRequest')->willReturn($response);

        $this->expectException(AARequestException::class);

        (new GetUserByPhone(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
            resolve(CarService::class),
        ))->handler($user);

        \Event::assertDispatched(SaveCarFromAA::class);
    }

    /** @test */
    public function not_user_in_aa_service()
    {
        // создаем пользователя
        $user = $this->userBuilder()->setPhone('380443311081')->create();
        $user->refresh();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('getRequest')->willThrowException(new AARequestException());

        \Event::fake([NotUserFromAA::class]);

        $this->assertEmpty($user->aaResponses);

        (new GetUserByPhone(
            $sender,
            resolve(ResponseService::class),
            resolve(UserService::class),
            resolve(CarService::class),
        ))->handler($user);

        $user->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertEquals($user->aaResponses[0]->status, AAResponse::STATUS_ERROR);
        $this->assertNull($user->uuid);

        \Event::assertDispatched(NotUserFromAA::class);
    }

    public static function successData(): array
    {
        return [
            "success" => true,
            "data" => [
                "user" => [
                    "id" => "35b0d8f2-f4f6-11eb-8274-4cd98fc26f15",
                    "name" => "ЕКОЛІС КИЇВ",
                    "number" => "+380443311080",
                    "codeOKPO" => "41885284",
                    "email" => "ecoles inna <ecoles.inna@gmail.com>",
                    "verified" => true
                ],
                "vechilces" =>[
                    [
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
                    [
                        "id" => "af3819c2-f908-11eb-8274-4cd98fc26f15",
                        "name" => "Logan Сірий № AI4921CK VI",
                        "brand" => "32a075c1-45e6-11e7-a80b-005056812a5f",
                        "model" => "c49359d9-de58-11eb-837f-000c29f1d0a8",
                        "year" => "2010",
                        "yearDeal" => "",
                        "vin" => "VF1KSRAB5440179",
                        "number" => "",
                        "owner" => "35b0d8f2-f4f6-11eb-8274-4cd98fc26f15",
                        "statusCar" => false,
                        "personal" => false,
                        "buy" => false,
                        "orderCar" => null,
                        "proxies" => null,
                        "verified" => false
                    ],
                    [
                        "id" => "051be3e1-f909-11eb-8274-4cd98fc26f15",
                        "name" => "Logan Сірий № AI4921CK VI",
                        "brand" => "32a075c1-45e6-11e7-a80b-005056812a5f",
                        "model" => "c49359d9-de58-11eb-837f-000c29f1d0a8",
                        "year" => "2010",
                        "yearDeal" => "",
                        "vin" => "VF1KSRAB5440179",
                        "number" => "",
                        "owner" => "35b0d8f2-f4f6-11eb-8274-4cd98fc26f15",
                        "statusCar" => false,
                        "personal" => false,
                        "buy" => false,
                        "orderCar" => null,
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
                        "verified" => false
                    ]
                ]
            ],
            "message" => ""
        ];
    }
}

