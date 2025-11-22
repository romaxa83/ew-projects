<?php

namespace Tests\Feature\Http\Api\V1\User;

use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class UserAddCarFromAATest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

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
    public function success_add()
    {
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $user = $this->userBuilder()->setUuid($userUuid)->create();

        $user->refresh();

        $brand = Brand::where('name',Brand::VOLVO)->first();
        $model = Model::where('name','S60')->first();
        $data = $this->data(
            $brand->uuid->getValue(),
            $model->uuid->getValue(),
        );

        $this->assertEmpty($user->cars);

        $response = $this->post(
            route('api.v1.user.car.add',[
                'userId' => $userUuid,
            ]),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $user->refresh();

        $this->assertNotEmpty($user->cars);
        $this->assertEquals($user->cars[0]->number->getValue(), data_get($data, 'number'));
        $this->assertEquals($user->cars[0]->vin->getValue(), data_get($data, 'vin'));
        $this->assertEquals($user->cars[0]->brand_id, $brand->id);
        $this->assertEquals($user->cars[0]->model_id, $model->id);
        $this->assertEquals($user->cars[0]->uuid, data_get($data, 'id'));
        $this->assertEquals($user->cars[0]->year, data_get($data, 'year'));
        $this->assertTrue($user->cars[0]->is_verify);
        $this->assertTrue($user->cars[0]->is_order);
        $this->assertFalse($user->cars[0]->in_garage);

        $this->assertNotNull($user->cars[0]->carOrder);
        $this->assertNotEmpty($user->cars[0]->confidants);
    }

    /** @test */
    public function success_without_proxies()
    {
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $user = $this->userBuilder()->setUuid($userUuid)->create();

        $user->refresh();

        $brand = Brand::where('name',Brand::VOLVO)->first();
        $model = Model::where('name','S60')->first();
        $data = $this->data(
            $brand->uuid->getValue(),
            $model->uuid->getValue(),
        );

        unset($data['proxies']);

        $this->assertEmpty($user->cars);

        $response = $this->post(
            route('api.v1.user.car.add',[
                'userId' => $userUuid,
            ]),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $user->refresh();

        $this->assertEmpty($user->cars[0]->confidants);
    }

    /** @test */
    public function success_without_order_car()
    {
        $userUuid = '35b0d8f2-f4f6-11eb-8274-4cd98fc26f15';
        $user = $this->userBuilder()->setUuid($userUuid)->create();

        $user->refresh();

        $brand = Brand::where('name',Brand::VOLVO)->first();
        $model = Model::where('name','S60')->first();
        $data = $this->data(
            $brand->uuid->getValue(),
            $model->uuid->getValue(),
        );

        unset($data['orderCar']);

        $this->assertEmpty($user->cars);

        $response = $this->post(
            route('api.v1.user.car.add',[
                'userId' => $userUuid,
            ]),
            $data,
            $this->headers()
        )
            ->assertOk()
        ;

        $this->assertEmpty($response->json('data'));
        $this->assertTrue($response->json('success'));

        $user->refresh();

        $this->assertFalse($user->cars[0]->is_order);
        $this->assertNull($user->cars[0]->carOrder);
    }

    private function data(
        string $brandUuid,
        string $modelUuid,
    ): array
    {
        return [
            "id" => "9ee4670f-0016-11ec-8274-4cd98fc26f15",
            "brand" => $brandUuid,
            "model" => $modelUuid,
            "year" => "2020",
            "yearDeal" => "2021",
            "vin" => "QW1243DF",
            "number" => "BX12421AM",
            "owner" => "35b0d8f2-f4f6-11eb-8274-4cd98fc26f15",
            "statusCar" => true,
            "personal" => true,
            "buy" => true,
            "orderCar" => [
                "orderNumber" => "25",
                "paymentStatusCar" => 1,
                "sumDiscount" => 145000,
                "sum" => 150000
            ],
            "proxies" => [
                [
                    "id" => "97ed860e-f4f4-11eb-8274-4cd98fc26f15",
                    "name" => "Кравчук Олег Петрович",
                    "number" => "0968381637"
                ],
            ],
        ];
    }
}




