<?php

namespace Tests\Unit\Services\User;

use App\Helpers\ConvertNumber;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\User\OrderCar\OrderStatus;
use App\Services\User\CarService;
use Arr;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class CarServiceTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    /** @test */
    public function success_create_from_aa_with_order_car_and_confidants()
    {
        $service = app(CarService::class);
        $user = $this->userBuilder()->withNotifications()->create();
        $user->refresh();

        $this->assertEmpty($user->cars);

        $brand = Brand::find(1);
        $model = Model::find(10);

        $data = $this->successDataFromAA();
        $data['vechilces'][0]['brand'] = $brand->uuid;
        $data['vechilces'][0]['model'] = $model->uuid;

        $service->createFromAA($user, $data['vechilces']);

        $user->refresh();

        $this->assertNotEmpty($user->cars);
        $car = $user->cars[0];

        $this->assertNotEquals($car->brand_id, $data['vechilces'][0]['brand']);
        $this->assertEquals($car->brand_id, $brand->id);
        $this->assertNotEquals($car->model_id, $data['vechilces'][0]['model']);
        $this->assertEquals($car->model_id, $model->id);

        $this->assertEquals($car->uuid, $data['vechilces'][0]['id']);
        $this->assertEquals($car->number, $data['vechilces'][0]['number']);
        $this->assertEquals($car->vin, $data['vechilces'][0]['vin']);
        $this->assertEquals($car->year, $data['vechilces'][0]['year']);
        $this->assertEquals($car->year_deal, $data['vechilces'][0]['yearDeal']);
        $this->assertEquals($car->is_buy, $data['vechilces'][0]['buy']);
        $this->assertEquals($car->is_personal, $data['vechilces'][0]['personal']);

        $this->assertEquals($car->aa_status, $data['vechilces'][0]['statusCar']);

        $this->assertFalse($car->is_add_to_app);
        $this->assertTrue($car->is_verify);
        $this->assertTrue($car->is_moderate);
        $this->assertFalse($car->in_garage);
        $this->assertFalse($car->selected);
        $this->assertTrue($car->is_order);

        $this->assertNotEmpty($car->carOrder);
        $this->assertEquals($car->carOrder->payment_status, $data['vechilces'][0]['orderCar']['paymentStatusCar']);
        $this->assertEquals(ConvertNumber::fromNumberToFloat($car->carOrder->sum_discount), $data['vechilces'][0]['orderCar']['sumDiscount']);
        $this->assertEquals($car->carOrder->order_number, $data['vechilces'][0]['orderCar']['orderNumber']);

        $statusesCount = OrderStatus::query()->count();

        $this->assertNotEmpty($car->carOrder->statuses);
        $this->assertEquals(count($car->carOrder->statuses), $statusesCount);

        // конф. лица

        $this->assertNotEmpty($user->cars[0]->confidants);
        $this->assertEquals($user->cars[0]->confidants[0]->uuid, Arr::get($data, 'vechilces.0.proxies.0.id'));
        $this->assertEquals($user->cars[0]->confidants[0]->name, Arr::get($data, 'vechilces.0.proxies.0.name'));
        $this->assertEquals($user->cars[0]->confidants[0]->phone, Arr::get($data, 'vechilces.0.proxies.0.number'));
    }

    public static function successDataFromAA()
    {
        return [
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
            ]
        ];
}
}

