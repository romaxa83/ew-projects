<?php

namespace Tests\Feature\Mutations\Order;

use App\Events\Order\CreateOrder;
use App\Exceptions\ErrorsCode;
use App\Listeners\Order\SendOrderToAAListeners;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Service\Service;
use App\Models\Recommendation\Recommendation;
use App\Types\Communication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\RecommendationBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class SparesCreateTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;
    use Statuses;
    use RecommendationBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_create()
    {
        \Event::fake([
            CreateOrder::class,
        ]);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();
        $this->loginAsUser($user);

        $user->refresh();
        $car->refresh();

        $service = Service::where('alias', Service::SPARES_ALIAS)->first();

        $data = [
            'carId' => $car->id,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
        ];

        $this->assertEmpty($user->aaResponses);
        $this->assertEmpty($user->orders);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.orderSparesCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('uuid', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('paymentStatus', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('name', $responseData['user']);
        $this->assertArrayHasKey('phone', $responseData['user']);
        $this->assertArrayHasKey('service', $responseData);
        $this->assertArrayHasKey('id', $responseData['service']);
        $this->assertArrayHasKey('name', $responseData['service']['current']);
        $this->assertArrayHasKey('communication', $responseData);
        $this->assertArrayHasKey('closedAt', $responseData);
        $this->assertArrayHasKey('deletedAt', $responseData);
        $this->assertArrayHasKey('createdAt', $responseData);
        $this->assertArrayHasKey('updatedAt', $responseData);

        $this->assertArrayHasKey('car', $responseData['additions']);
        $this->assertArrayHasKey('id',  data_get($responseData, 'additions.car'));

        $this->assertArrayHasKey('recommendation', $responseData['additions']);

        $this->assertArrayHasKey('comment',  data_get($responseData, 'additions'));
        $this->assertArrayHasKey('onDate',  data_get($responseData, 'additions'));

        $this->assertNull($responseData['uuid']);
        $this->assertEquals($service->id, $responseData['service']['id']);
        $this->assertEquals($user->name, $responseData['user']['name']);
        $this->assertEquals(Communication::PHONE, $responseData['communication']);
        $this->assertNull($responseData['closedAt']);
        $this->assertNull($responseData['deletedAt']);
        $this->assertNotNull($responseData['createdAt']);
        $this->assertNotNull($responseData['updatedAt']);
        $this->assertEquals($this->order_status_draft, $responseData['status']);
        $this->assertEquals($this->order_payment_status_not, $responseData['paymentStatus']);
        $this->assertEquals($this->order_type_ordinary, $responseData['type']);

        $this->assertNull(data_get($responseData, 'additions.recommendation'));

        $this->assertEquals($car->id, data_get($data, 'carId'));
        $this->assertEquals(data_get($responseData, 'additions.comment'), data_get($data, 'comment'));

        $this->assertEquals(data_get($responseData, 'additions.brand.name'), $car->brand->name);
        $this->assertEquals(data_get($responseData, 'additions.model.name'), $car->model->name);

        $user->refresh();
        $this->assertNotEmpty($user->orders);

        \Event::assertDispatched(CreateOrder::class);
        \Event::assertListening(CreateOrder::class, SendOrderToAAListeners::class);

        $order = $user->orders[0];

        \Event::assertDispatched(function (CreateOrder $event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    /** @test */
    public function success_create_with_recommendation()
    {
        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();
        /** @var $recommendation Recommendation */
        $recommendation = $this->recommendationBuilder()->create();
        $recommendation->refresh();

        $this->loginAsUser($user);

        $data = [
            'carId' => $car->id,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
            'recommendationId' => $recommendation->id,
        ];

        $this->assertTrue($recommendation->isNew());

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithRecommendation($data)])
            ->assertOk();

        $responseData = $response->json('data.orderSparesCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('type', $responseData);

        $this->assertArrayHasKey('recommendation', data_get($responseData, 'additions'));
        $this->assertArrayHasKey('id', data_get($responseData, 'additions.recommendation'));

        $this->assertEquals($this->order_type_recommend, $responseData['type']);
        $this->assertEquals($recommendation->id, data_get($data, 'recommendationId'));

        $user->refresh();
        $order = $user->orders[0];

        $this->assertNotNull($order->additions->recommendation);
        $this->assertEquals($order->additions->recommendation->id, $recommendation->id);

        $recommendation->refresh();
        $this->assertTrue($recommendation->isUsed());
    }

    /** @test */
    public function not_verify_car()
    {
        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->notVerify()->setUserId($user->id)->create();
        $this->loginAsUser($user);

        $user->refresh();
        $car->refresh();

        $data = [
            'carId' => $car->id,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
        ];

        $this->assertFalse($car->isVerify());

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('error.order.car must be verify'),
            $response->json('errors.0.message')
        );
    }

    /** @test */
    public function fail_brand_no_main()
    {
        $brand = Brand::find(1);
        $brand->is_main = false;
        $brand->save();

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setBrandId($brand->id)->setUserId($user->id)->create();
        $this->loginAsUser($user);

        $user->refresh();
        $car->refresh();

        $data = [
            'carId' => $car->id,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.order.order not support brand', ['brand' => $brand->name]), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                orderSparesCreate(input:{
                    carId: %d
                    communication: "%s"
                    comment: "%s"
                }) {
                    id
                    uuid
                    type
                    status
                    paymentStatus
                    user {
                        name
                        phone
                    }
                    service {
                        id
                        current {
                            name
                        }
                    }
                    communication
                    additions {
                        car {
                            id
                        }
                        dealership {
                            id
                        }
                        brand {
                            name
                        }
                        model {
                            name
                        }
                        recommendation {
                            id
                        }
                        comment
                        mileage
                        onDate
                    }
                    closedAt
                    deletedAt
                    createdAt
                    updatedAt
                }
            }',
            $data['carId'],
            $data['communication'],
            $data['comment'],
        );
    }

    private function getQueryStrWithRecommendation(array $data): string
    {
        return sprintf('
            mutation {
                orderSparesCreate(input:{
                    carId: %d
                    communication: "%s"
                    comment: "%s"
                    recommendationId: %d
                }) {
                    id
                    uuid
                    type
                    additions {
                        recommendation {
                            id
                        }
                    }
                }
            }',
            $data['carId'],
            $data['communication'],
            $data['comment'],
            $data['recommendationId'],
        );
    }
}




