<?php

namespace Tests\Feature\Queries\History;

use App\DTO\History\HistoryCarDto;
use App\Exceptions\ErrorsCode;
use App\Services\History\CarHistoryService;
use Faker\Generator as Faker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\HistoryTestData;
use Tests\Traits\UserBuilder;

class ItemPaginatorTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CarBuilder;
    use UserBuilder;
    use HistoryTestData;

    const QUERY = 'histories';

    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();

        $this->faker = resolve(Faker::class);
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        // создаем запись
        $service = app(CarHistoryService::class);
        $service->createOrUpdate(HistoryCarDto::byRequest($this->dataCustom($this->faker)));
        $service->createOrUpdate(HistoryCarDto::byRequest($this->dataCustom($this->faker)));

        $this->graphQL($this->getQueryStr())
            ->assertJsonStructure([
                "data" => [
                    self::QUERY =>  [
                        "data" => [
                            "*" => ["id"]
                        ],
                        "paginatorInfo" => [
                            "count",
                            "total",
                        ]
                    ]
                ]
            ])
            ->assertJson([
                "data" => [
                    self::QUERY => [
                        "paginatorInfo" => [
                            "count" => 2,
                            "total" => 2,
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_user_id()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $userUuid_1 = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user_1 = $this->userBuilder()->setUuid($userUuid_1)->create();
        $userUuid_2 = "2ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user_2 = $this->userBuilder()->setUuid($userUuid_2)
            ->setPhone('38099999888871')->setEmail('test1@user.com')->create();

        $carUuid_1 = "1ee4670f-0016-11ec-8274-4cd98fc26f14";
        $car_1 = $this->carBuilder()->setUserId($user_1->id)->setUuid($carUuid_1)->create();
        $carUuid_2 = "2ee4670f-0016-11ec-8274-4cd98fc26f14";
        $car_2 = $this->carBuilder()->setUserId($user_1->id)->setUuid($carUuid_2)->create();
        $carUuid_3 = "3ee4670f-0016-11ec-8274-4cd98fc26f14";
        $car_3 = $this->carBuilder()->setUserId($user_2->id)->setUuid($carUuid_3)->create();

        // создаем запись
        $service = app(CarHistoryService::class);

        $data_1 = $this->data($this->faker);
        $data_1['id'] = $carUuid_1;

        $data_2 = $this->data($this->faker);
        $data_2['id'] = $carUuid_2;

        $data_3 = $this->data($this->faker);
        $data_3['id'] = $carUuid_3;
        // user_1
        $rec_1 = $service->createOrUpdate(HistoryCarDto::byRequest($data_1));
        $rec_2 = $service->createOrUpdate(HistoryCarDto::byRequest($data_2));
        // user_2
        $rec_3 = $service->createOrUpdate(HistoryCarDto::byRequest($data_3));

        $this->graphQL($this->getQueryStrWithUserID($user_1->id))
            ->assertJson([
                "data" => [
                    self::QUERY => [
                        "data" => [
                            ["id" => $rec_1->id],
                            ["id" => $rec_2->id],
                        ],
                        "paginatorInfo" => [
                            "count" => 2,
                            "total" => 2,
                        ]
                    ]
                ]
            ])
        ;

        $this->graphQL($this->getQueryStrWithUserID($user_2->id))
            ->assertJson([
                "data" => [
                    self::QUERY => [
                        "data" => [
                            ["id" => $rec_3->id],
                        ],
                        "paginatorInfo" => [
                            "count" => 1,
                            "total" => 1,
                        ]
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

        $this->graphQL($this->getQueryStr())
            ->assertJson([
                "data" => [
                    self::QUERY => [
                        "paginatorInfo" => [
                            "count" => 0,
                            "total" => 0,
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->graphQL($this->getQueryStr())
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

    public function getQueryStr(): string
    {
        return  sprintf('{
            %s {
                data{
                    id
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
            self::QUERY,
        );
    }

    public function getQueryStrWithUserID($ID): string
    {
        return  sprintf('{
            %s (userId: %s) {
                data{
                    id
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
            self::QUERY,
        $ID
        );
    }
}




