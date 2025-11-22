<?php

namespace Tests\Feature\Mutations\Order;

use App\Events\Order\CreateOrder;
use App\Exceptions\ErrorsCode;
use App\Listeners\Order\SendOrderToAAListeners;
use App\Models\AA\AAPost;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Models\Recommendation\Recommendation;
use App\Types\Communication;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\AAPostBuilder;
use Tests\Traits\Builders\RecommendationBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class ServiceCreateTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;
    use Statuses;
    use RecommendationBuilder;
    use AAPostBuilder;

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

        $service = Service::where('alias', Service::SERVICE_TO_ALIAS)->first();
        $dealership = Dealership::find(1);

        $date = CarbonImmutable::now();
        /** @var $post AAPost */
        $post = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        // date = 2021-09-06, time = 10:00:00
        $data = [
            'carId' => $car->id,
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'mileage' => 1110,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
            'date' => "1630875600000",
            'time' => "36000000",
            'postUuid' => $post->uuid
        ];

        $this->assertEmpty($user->aaResponses);
        $this->assertEmpty($user->orders);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.orderServiceCreate');

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
        $this->assertArrayHasKey('admin', $responseData);
        $this->assertArrayHasKey('communication', $responseData);
        $this->assertArrayHasKey('closedAt', $responseData);
        $this->assertArrayHasKey('deletedAt', $responseData);
        $this->assertArrayHasKey('createdAt', $responseData);
        $this->assertArrayHasKey('updatedAt', $responseData);

        $this->assertArrayHasKey('typeUser', $responseData['additions']);
        $this->assertArrayHasKey('car', $responseData['additions']);
        $this->assertArrayHasKey('id',  data_get($responseData, 'additions.car'));

        $this->assertArrayHasKey('dealership',  data_get($responseData, 'additions'));
        $this->assertArrayHasKey('id',  data_get($responseData, 'additions.dealership'));

        $this->assertArrayHasKey('comment',  data_get($responseData, 'additions'));
        $this->assertArrayHasKey('mileage',  data_get($responseData, 'additions'));
        $this->assertArrayHasKey('onDate',  data_get($responseData, 'additions'));

        $this->assertArrayHasKey('recommendation',  data_get($responseData, 'additions'));

        $this->assertEquals("2021-09-06 10:00:00", data_get($responseData, 'additions.onDate'));

        $this->assertNull($responseData['uuid']);
        $this->assertEquals($service->id, $responseData['service']['id']);
        $this->assertEquals($user->name, $responseData['user']['name']);
        $this->assertNull($responseData['admin']);
        $this->assertEquals(Communication::PHONE, $responseData['communication']);
        $this->assertNull($responseData['closedAt']);
        $this->assertNull($responseData['deletedAt']);
        $this->assertNotNull($responseData['createdAt']);
        $this->assertNotNull($responseData['updatedAt']);
        $this->assertEquals($this->order_status_draft, $responseData['status']);
        $this->assertEquals($this->order_payment_status_not, $responseData['paymentStatus']);

        $this->assertEquals($this->order_type_ordinary, $responseData['type']);

        $this->assertEquals($car->id, data_get($data, 'carId'));
        $this->assertEquals($dealership->id, data_get($data, 'dealershipId'));
        $this->assertEquals(data_get($responseData, 'additions.comment'), data_get($data, 'comment'));
        $this->assertEquals(data_get($responseData, 'additions.mileage'), data_get($data, 'mileage'));

        $this->assertEquals(data_get($responseData, 'additions.brand.name'), $car->brand->name);
        $this->assertEquals(data_get($responseData, 'additions.model.name'), $car->model->name);

        $user->refresh();
        $this->assertNotEmpty($user->orders);

        \Event::assertDispatched(CreateOrder::class);
        \Event::assertListening(CreateOrder::class, SendOrderToAAListeners::class);

        $order = $user->orders[0];

        $this->assertNotNull($order->additions->for_current_filter_date);
        $this->assertEquals($order->additions->for_current_filter_date, $order->additions->on_date);
        $this->assertEquals($order->additions->post_uuid, $post->uuid);

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

        $service = Service::where('alias', Service::SERVICE_TO_ALIAS)->first();
        $dealership = Dealership::find(1);

        $date = CarbonImmutable::now();
        /** @var $post AAPost */
        $post = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        // date = 2021-09-06, time = 10:00:00
        $data = [
            'carId' => $car->id,
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'mileage' => 1110,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
            'date' => "1630875600000",
            'time' => "36000000",
            "recommendationId" => $recommendation->id,
            'postUuid' => $post->uuid
        ];

        $this->assertTrue($recommendation->isNew());

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithRecommendation($data)])
            ->assertOk();

        $responseData = $response->json('data.orderServiceCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('type', $responseData);

        $this->assertArrayHasKey('recommendation', $responseData['additions']);
        $this->assertArrayHasKey('id',  data_get($responseData, 'additions.recommendation'));

        $this->assertEquals($recommendation->id, data_get($responseData, 'additions.recommendation.id'));

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

        $service = Service::where('alias', Service::SERVICE_TO_ALIAS)->first();
        $dealership = Dealership::find(1);

        $date = CarbonImmutable::now();
        /** @var $post AAPost */
        $post = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        // date = 2021-09-06, time = 10:00:00
        $data = [
            'carId' => $car->id,
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'mileage' => 1110,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
            'date' => "1630875600000",
            'time' => "36000000",
            'postUuid' => $post->uuid
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
    public function fail_date_no_numeric()
    {
        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();
        $this->loginAsUser($user);

        $user->refresh();
        $car->refresh();

        $dealership = Dealership::find(1);
        $service = Service::where('alias', Service::SERVICE_TO_ALIAS)->first();

        $date = CarbonImmutable::now();
        /** @var $post AAPost */
        $post = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        // date = 2021-09-06, time = 10:00:00
        $data = [
            'carId' => $car->id,
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'mileage' => 1110,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
            'date' => "fail",
            'time' => "3600000",
            'postUuid' => $post->uuid
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('validation.numeric', ['attribute' => __('validation.attributes.date')]),
            $response->json('errors.0.message')
        );
    }

    /** @test */
    public function fail_date_very_big()
    {
        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();
        $this->loginAsUser($user);

        $user->refresh();
        $car->refresh();

        $dealership = Dealership::find(1);
        $service = Service::where('alias', Service::SERVICE_TO_ALIAS)->first();

        $date = CarbonImmutable::now();
        /** @var $post AAPost */
        $post = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        // date = 2021-09-06, time = 10:00:00
        $data = [
            'carId' => $car->id,
            'dealershipId' => $dealership->id,
            'serviceId' => $service->id,
            'communication' => Communication::PHONE,
            'mileage' => 1110,
            'comment' => 'some comment',
            'date' => "16308756000000",
            'time' => "3600000",
            'postUuid' => $post->uuid
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('validation.digits', [
                'attribute' => __('validation.attributes.date'),
                'digits' => 13
            ]),
            $response->json('errors.0.message')
        );
    }

    /** @test */
    public function fail_date_very_small()
    {
        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();
        $this->loginAsUser($user);

        $user->refresh();
        $car->refresh();

        $dealership = Dealership::find(1);
        $service = Service::where('alias', Service::SERVICE_TO_ALIAS)->first();

        $date = CarbonImmutable::now();
        /** @var $post AAPost */
        $post = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        // date = 2021-09-06, time = 10:00:00
        $data = [
            'carId' => $car->id,
            'dealershipId' => $dealership->id,
            'serviceId' => $service->id,
            'communication' => Communication::PHONE,
            'mileage' => 1110,
            'comment' => 'some comment',
            'date' => "1630875600",
            'time' => "3600000",
            'postUuid' => $post->uuid
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('validation.digits', [
                'attribute' => __('validation.attributes.date'),
                'digits' => 13
            ]),
            $response->json('errors.0.message')
        );
    }

    /** @test */
    public function fail_time_no_numeric()
    {
        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();
        $this->loginAsUser($user);

        $user->refresh();
        $car->refresh();

        $dealership = Dealership::find(1);
        $service = Service::where('alias', Service::SERVICE_TO_ALIAS)->first();

        $date = CarbonImmutable::now();
        /** @var $post AAPost */
        $post = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        // date = 2021-09-06, time = 10:00:00
        $data = [
            'carId' => $car->id,
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'mileage' => 1110,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
            'date' => "1630875600000",
            'time' => "fail",
            'postUuid' => $post->uuid
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('validation.numeric', ['attribute' => __('validation.attributes.time')]),
            $response->json('errors.0.message')
        );
    }

    /** @test */
    public function fail_time_very_big()
    {
        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();
        $this->loginAsUser($user);

        $user->refresh();
        $car->refresh();

        $dealership = Dealership::find(1);
        $service = Service::where('alias', Service::SERVICE_TO_ALIAS)->first();

        $date = CarbonImmutable::now();
        /** @var $post AAPost */
        $post = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        // date = 2021-09-06, time = 10:00:00
        $data = [
            'carId' => $car->id,
            'dealershipId' => $dealership->id,
            'serviceId' => $service->id,
            'communication' => Communication::PHONE,
            'mileage' => 1110,
            'comment' => 'some comment',
            'date' => "1630875600000",
            'time' => "360000055599",
            'postUuid' => $post->uuid
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('validation.digits_between', [
                'attribute' => __('validation.attributes.time'),
                'min' => 6,
                'max' => 8,
            ]),
            $response->json('errors.0.message')
        );
    }

    /** @test */
    public function fail_time_very_small()
    {
        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();
        $this->loginAsUser($user);

        $user->refresh();
        $car->refresh();

        $dealership = Dealership::find(1);
        $service = Service::where('alias', Service::SERVICE_TO_ALIAS)->first();

        $date = CarbonImmutable::now();
        /** @var $post AAPost */
        $post = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        // date = 2021-09-06, time = 10:00:00
        $data = [
            'carId' => $car->id,
            'dealershipId' => $dealership->id,
            'serviceId' => $service->id,
            'communication' => Communication::PHONE,
            'mileage' => 1110,
            'comment' => 'some comment',
            'date' => "1630875600000",
            'time' => "360",
            'postUuid' => $post->uuid
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('validation.digits_between', [
                'attribute' => __('validation.attributes.time'),
                'min' => 6,
                'max' => 8,
            ]),
            $response->json('errors.0.message')
        );
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                orderServiceCreate(input:{
                    carId: %d
                    serviceId: %d
                    dealershipId: %d
                    mileage: %d
                    communication: "%s"
                    comment: "%s"
                    date: "%s"
                    time: "%s"
                    postUuid: "%s"
                }) {
                    id
                    uuid
                    status
                    type
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
                    admin {
                        name
                    }
                    communication
                    additions {
                        typeUser
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
            $data['serviceId'],
            $data['dealershipId'],
            $data['mileage'],
            $data['communication'],
            $data['comment'],
            $data['date'],
            $data['time'],
            $data['postUuid'],
        );
    }

    private function getQueryStrWithRecommendation(array $data): string
    {
        return sprintf('
            mutation {
                orderServiceCreate(input:{
                    carId: %d
                    serviceId: %d
                    dealershipId: %d
                    recommendationId: %d
                    mileage: %d
                    communication: "%s"
                    comment: "%s"
                    date: "%s"
                    time: "%s",
                    postUuid: "%s"
                }) {
                    id
                    type
                    additions {
                        typeUser
                        recommendation {
                            id
                        }
                    }
                }
            }',
            $data['carId'],
            $data['serviceId'],
            $data['dealershipId'],
            $data['recommendationId'],
            $data['mileage'],
            $data['communication'],
            $data['comment'],
            $data['date'],
            $data['time'],
            $data['postUuid'],
        );
    }
}


