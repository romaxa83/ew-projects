<?php

namespace Tests\Feature\Queries\User;

use App\Models\Promotion\Promotion;
use App\Types\Order\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\PromotionBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class AuthUserTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;
    use CarBuilder;
    use OrderBuilder;
    use PromotionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function auth_success()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.authUser');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('createdAt', $responseData);
        $this->assertArrayHasKey('egrpoy', $responseData);
        $this->assertArrayHasKey('lang', $responseData);
        $this->assertArrayHasKey('locale', $responseData);
        $this->assertArrayHasKey('locale', $responseData['locale']);
        $this->assertArrayHasKey('slug', $responseData['locale']);
        $this->assertArrayHasKey('name', $responseData['locale']);
        $this->assertArrayHasKey('avatar', $responseData);
        $this->assertArrayHasKey('fcmNotifications', $responseData);
        $this->assertArrayHasKey('data', $responseData['fcmNotifications']);
        $this->assertArrayHasKey('paginatorInfo', $responseData['fcmNotifications']);
        $this->assertArrayHasKey('orders', $responseData);
        $this->assertArrayHasKey('promotions', $responseData);

        $this->assertEquals($responseData['id'], $user->id);
        $this->assertEmpty($responseData['avatar']);
        $this->assertEmpty($responseData['fcmNotifications']['data']);
        $this->assertEmpty($responseData['cars']);
        $this->assertEmpty($responseData['selectedCar']);
        $this->assertEmpty($responseData['orders']);
        $this->assertEmpty($responseData['promotions']);
    }

    /** @test */
    public function auth_success_with_notification()
    {
        $user = $this->userBuilder()->withNotifications()->create();
        $this->loginAsUser($user);
        $count = $user->fcmNotifications->count();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.authUser');

        $this->assertArrayHasKey('fcmNotifications', $responseData);
        $this->assertArrayHasKey('data', $responseData['fcmNotifications']);
        $this->assertArrayHasKey('paginatorInfo', $responseData['fcmNotifications']);
        $this->assertArrayHasKey('id', $responseData['fcmNotifications']['data'][0]);
        $this->assertArrayHasKey('status', $responseData['fcmNotifications']['data'][0]);
        $this->assertArrayHasKey('type', $responseData['fcmNotifications']['data'][0]);
        $this->assertArrayHasKey('sendData', $responseData['fcmNotifications']['data'][0]);


        $this->assertNotEmpty($responseData['fcmNotifications']['data']);
        $this->assertCount($count, $responseData['fcmNotifications']['data']);
        $this->assertEquals($count, $responseData['fcmNotifications']['paginatorInfo']['total']);
    }

    /** @test */
    public function auth_success_with_cars()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $carBuilder = $this->carBuilder();
        $car1 = $carBuilder->setUserId($user->id)->selected()->withConfidant()->create();
        $car2 = $carBuilder->setUserId($user->id)->create();
        $car3 = $carBuilder->setUserId($user->id)->create();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.authUser');

        $car1->refresh();

        $this->assertArrayHasKey('cars', $responseData);
        $this->assertArrayHasKey('id', $responseData['cars'][0]);
        $this->assertCount(3, $responseData['cars']);
        $this->assertArrayHasKey('selectedCar', $responseData);
        $this->assertArrayHasKey('id', $responseData['selectedCar']);
        $this->assertEquals($car1->id, $responseData['selectedCar']['id']);
        $this->assertArrayHasKey('confidants', $responseData['selectedCar']);
        $this->assertArrayHasKey('name', $responseData['selectedCar']['confidants'][0]);
        $this->assertArrayHasKey('phone', $responseData['selectedCar']['confidants'][0]);
    }

    /** @test */
    public function auth_success_with_cars_without_selected()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $carBuilder = $this->carBuilder();
        $carBuilder->setUserId($user->id)->create();
        $carBuilder->setUserId($user->id)->create();
        $carBuilder->setUserId($user->id)->create();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.authUser');

        $this->assertNull($responseData['selectedCar']);
    }

    /** @test */
    public function auth_success_with_orders()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setStatus(Status::IN_PROCESS)->setUserId($user->id)->setCount(3)->create();
        $orderBuilder->setStatus(Status::CREATED)->setUserId($user->id)->setCount(3)->create();

        $this->assertNotEmpty($user->orders);

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.authUser');

        $this->assertArrayHasKey('orders', $responseData);
        $this->assertArrayHasKey('id', $responseData['orders'][0]);
        $this->assertArrayHasKey('service', $responseData['orders'][0]);
        $this->assertArrayHasKey('name', $responseData['orders'][0]['service']['current']);

        $this->assertCount(6, $responseData['orders']);
    }

    /** @test */
    public function auth_success_with_history_orders()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setStatus(Status::DONE)->setUserId($user->id)->setCount(2)->create();
        $orderBuilder->setStatus(Status::CLOSE)->setUserId($user->id)->setCount(2)->create();

        $this->assertNotEmpty($user->orders);

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.authUser');

        $this->assertArrayHasKey('ordersHistory', $responseData);
        $this->assertArrayHasKey('ordersCurrent', $responseData);

        $this->assertNotEmpty($responseData['ordersCurrent']);
        $this->assertNotEmpty($responseData['ordersHistory']);

        $this->assertCount(2, $responseData['ordersHistory']);
    }

    /** @test */
    public function auth_success_with_current_orders()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        $orderBuilder = $this->orderBuilder();
        $orderBuilder->setStatus(Status::CREATED)->setUserId($user->id)->setCount(1)->create();
        $orderBuilder->setStatus(Status::IN_PROCESS)->setUserId($user->id)->setCount(1)->create();
        $orderBuilder->setStatus(Status::DRAFT)->setUserId($user->id)->setCount(1)->create();
        $orderBuilder->setStatus(Status::DONE)->setUserId($user->id)->setCount(2)->create();

        $orderBuilder->setStatus(Status::CLOSE)->setUserId($user->id)->setCount(2)->create();

        $this->assertNotEmpty($user->orders);

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.authUser');

        $this->assertArrayHasKey('ordersHistory', $responseData);
        $this->assertArrayHasKey('ordersCurrent', $responseData);

        $this->assertNotEmpty($responseData['ordersCurrent']);
        $this->assertNotEmpty($responseData['ordersHistory']);

        $this->assertCount(5, $responseData['ordersCurrent']);
    }

    /** @test */
    public function auth_success_with_promotions()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $promotionBuilder = $this->promotionBuilder();
        $promotionBuilder->setType(Promotion::TYPE_INDIVIDUAL)->setUsersId([$user->id])->create();
        $promotionBuilder->setType(Promotion::TYPE_INDIVIDUAL)->setUsersId([$user->id])->create();
        $promotionBuilder->setType(Promotion::TYPE_INDIVIDUAL)->create();

        $this->assertNotEmpty($user->promotions);

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.authUser');

        $this->assertArrayHasKey('promotions', $responseData);
        $this->assertArrayHasKey('id', $responseData['promotions'][0]);
        $this->assertArrayHasKey('name', $responseData['promotions'][0]['current']);

        $this->assertCount(2, $responseData['promotions']);
    }

    /** @test */
    public function auth_wrong()
    {
        $user = $this->userBuilder()->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            authUser {
                id
                email
                phone
                name
                status
                createdAt
                egrpoy
                lang
                locale {
                    name
                    slug
                    locale
                }
                avatar {
                    id
                    url
                    type
                    sizes
                },
                fcmNotifications {
                    data {
                        id
                        status
                        type
                        sendData
                    }
                    paginatorInfo {
                        total
                    }
                },
                cars {
                    id
                }
                selectedCar {
                    id
                    confidants {
                        name
                        phone
                    }
                }
                orders {
                    id
                    service {
                        current {
                            name
                        }
                    }
                }
                ordersCurrent {
                    id
                    status
                }
                ordersHistory {
                    id
                    status
                }
                promotions {
                    id
                    current {
                        name
                    }
                }
               }
            }'
        );
    }
}


