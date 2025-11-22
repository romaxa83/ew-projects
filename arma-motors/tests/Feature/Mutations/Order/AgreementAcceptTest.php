<?php

namespace Tests\Feature\Mutations\Order;

use App\Events\Order\AcceptAgreementEvent;
use App\Listeners\Order\AcceptAgreementListeners;
use App\Models\Agreement\Agreement;
use App\Models\Dealership\Dealership;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\AgreementBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class AgreementAcceptTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;
    use OrderBuilder;
    use Statuses;
    use AgreementBuilder;

    const QUERY = 'agreementAccept';

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        \Event::fake([AcceptAgreementEvent::class]);

        $userUuid = "1ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $this->loginAsUser($user);

        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $orderUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $order = $this->orderBuilder()->setUuid($orderUuid)->asOne()->create();

        /** @var $model Agreement */
        $model = $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->setUserUuid($userUuid)
            ->setBaseOrderUuid($orderUuid)
            ->create();

        $this->assertEquals($order->agreements[0]->id, $model->id);
        $this->assertEmpty($order->agreementsAccept);
        $this->assertEquals($model->baseOrder->uuid, $order->uuid);

        $dealership = Dealership::query()->first();

        $data = [
            "id" => $model->id,
            "dealershipId" => $dealership->id,
        ];

        $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertJson(["data" => [
                self::QUERY => [
                    "id" => $model->id,
                    "uuid" => $model->uuid,
                    "status" => "USED",
                    "user" => [
                        "id" => $user->id
                    ],
                    "phone" => $model->phone,
                    "baseOrder" => [
                        "id" => $order->id
                    ]
                ]
            ]])
            ;

        \Event::assertDispatched(AcceptAgreementEvent::class);
        \Event::assertListening(
            AcceptAgreementEvent::class,
            AcceptAgreementListeners::class
        );

        \Event::assertDispatched(function (AcceptAgreementEvent $event) use ($model) {
            return $event->model->id === $model->id;
        });
    }

    /** @test */
    public function success_not_base_order()
    {
        \Event::fake([AcceptAgreementEvent::class]);

        $userUuid = "1ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $this->loginAsUser($user);

        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $orderUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $order = $this->orderBuilder()->setUuid($orderUuid)->asOne()->create();

        /** @var $model Agreement */
        $model = $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->setUserUuid($userUuid)
            ->create();

        $this->assertEmpty($order->agreementsAccept);
        $this->assertNull($model->baseOrder);

        $dealership = Dealership::query()->first();

        $data = [
            "id" => $model->id,
            "dealershipId" => $dealership->id,
        ];

        $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertJson(["data" => [
                self::QUERY => [
                    "id" => $model->id,
                    "uuid" => $model->uuid,
                    "status" => "USED",
                    "user" => [
                        "id" => $user->id
                    ],
                    "phone" => $model->phone,
                    "baseOrder" => null
                ]
            ]])
        ;

        \Event::assertDispatched(AcceptAgreementEvent::class);
        \Event::assertListening(
            AcceptAgreementEvent::class,
            AcceptAgreementListeners::class
        );

        \Event::assertDispatched(function (AcceptAgreementEvent $event) use ($model) {
            return $event->model->id === $model->id;
        });
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                %s(input:{
                    id: %s
                    dealershipId: %s
                }) {
                    id
                    status
                    uuid
                    user {
                        id
                    }
                    phone
                    number
                    vin
                    author
                    authorPhone
                    baseOrder {
                        id
                    }
                }
            }',
            self::QUERY,
            $data['id'],
            $data['dealershipId'],
        );
    }

}




