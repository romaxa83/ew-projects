<?php

namespace Tests\Feature\Queries\Agreement;

use App\Exceptions\ErrorsCode;
use App\Models\Agreement\Agreement;
use App\Models\Dealership\Dealership;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\AgreementBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class AgreementOneTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CarBuilder;
    use UserBuilder;
    use AgreementBuilder;
    use OrderBuilder;

    const QUERY = 'agreement';

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();
        $dealership = Dealership::find(1);
        $orderUuid = '76e6f86a-a9cb-11ec-827c-4cd98fc26f14';
        $order = $this->orderBuilder()->setUuid($orderUuid)->asOne()->create();
        /** @var $model Agreement */
        $model = $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setCarUuid($carUuid)
            ->setDealershipAlias($dealership->alias)
            ->setBaseOrderUuid($orderUuid)
            ->create();

        $this->graphQL($this->getQueryStr($model->id))
            ->assertJson([
                "data" => [
                    self::QUERY => [
                        "id" => $model->id,
                        "uuid" => $model->uuid,
                        "user" => [
                            "id" => $user->id,
                            "uuid" => $user->uuid,
                        ],
                        "car" => [
                            "id" => $car->id,
                            "uuid" => $car->uuid,
                        ],
                        "dealership" => [
                            "id" => $dealership->id
                        ],
                        "baseOrder" => [
                            "id" => $order->id,
                            "uuid" => $orderUuid,
                        ],
                        "phone" => $model->phone,
                        "number" => $model->number,
                        "vin" => $model->vin,
                        "author" => $model->author,
                        "authorPhone" => $model->author_phone,
                        "jobs" => [
                            [
                                "id" => $model->jobs[0]->id,
                                "name" => $model->jobs[0]->name,
                                "sum" => $model->jobs[0]->sum
                            ],
                            [
                                "id" => $model->jobs[1]->id,
                                "name" => $model->jobs[1]->name,
                                "sum" => $model->jobs[1]->sum
                            ]
                        ],
                        "parts" => [
                            [
                                "id" => $model->parts[0]->id,
                                "name" => $model->parts[0]->name,
                                "sum" => $model->parts[0]->sum,
                                "qty" => $model->parts[0]->qty
                            ],
                            [
                                "id" => $model->parts[1]->id,
                                "name" => $model->parts[1]->name,
                                "sum" => $model->parts[1]->sum,
                                "qty" => $model->parts[1]->qty,
                            ]
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function not_model()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $this->graphQL($this->getQueryStr(9999))
            ->assertJson([
                "data" => [
                    self::QUERY => null
                ]
            ])
        ;
    }


    public function not_auth()
    {
        $this->graphQL($this->getQueryStr(9999))
            ->assertJson([
                "errors" => [
                    0 => [
                        "message" => __('auth.not auth'),
                        "extensions" => [
                            "code" => ErrorsCode::NOT_AUTH
                        ]
                    ]
                ]
            ])
        ;
    }

    public function getQueryStr(string $id): string
    {
        return  sprintf('{
            %s (id: %s) {
                id
                uuid
                user {
                    id
                    uuid
                }
                car {
                    id
                    uuid
                }
                dealership {
                    id
                }
                baseOrder {
                    id
                    uuid
                }
                phone
                number
                vin
                author
                authorPhone
                jobs {
                    id
                    name
                    sum
                },
                parts {
                    id
                    name
                    sum
                    qty
                }
                createdAt
                updatedAt
               }
            }',
            self::QUERY,
            $id
        );
    }
}


