<?php

namespace Tests\Feature\Queries\Agreement;

use App\Models\Agreement\Agreement;
use App\Types\Order\Status;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\AgreementBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class AgreementAcceptForMobile extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;
    use OrderBuilder;
    use AgreementBuilder;
    use Statuses;

    const QUERY = 'agreementsAccept';

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $this->loginAsUser($user);

        // yes
        $model = $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setStatus(Agreement::STATUS_VERIFY)
            ->setCarUuid($carUuid)
            ->create();
        // no
        $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setStatus(Agreement::STATUS_NEW)
            ->setCarUuid($carUuid)
            ->create();
        // no
        $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setStatus(Agreement::STATUS_USED)
            ->setCarUuid($carUuid)
            ->create();
        // no
        $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setStatus(Agreement::STATUS_ERROR)
            ->setCarUuid($carUuid)
            ->create();
        // no
        $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->create();

        $this->graphQL($this->getQueryStr($user->id))
            ->assertJson([
                "data" => [
                    self::QUERY => [
                        "data" => [
                            ["id" => $model->id]
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
    public function success_other_user()
    {
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $userUuid2 = "7ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $user2 = $this->userBuilder()->setPhone('38099999888871')
            ->setEmail('test1@user.com')->setUuid($userUuid2)->create();
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $this->loginAsUser($user);

        // yes
        $model = $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setStatus(Agreement::STATUS_VERIFY)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setUserUuid($userUuid2)
            ->setStatus(Agreement::STATUS_VERIFY)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->setStatus(Agreement::STATUS_VERIFY)
            ->create();

        $this->graphQL($this->getQueryStr($user->id))
            ->assertJson([
                "data" => [
                    self::QUERY => [
                        "data" => [
                            ["id" => $model->id]
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
    public function success_empty()
    {
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $userUuid2 = "7ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $user2 = $this->userBuilder()->setPhone('38099999888871')
            ->setEmail('test1@user.com')->setUuid($userUuid2)->create();
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $this->loginAsUser($user);

        // not
        $this->agreementBuilder()
            ->setUserUuid($userUuid2)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setUserUuid($userUuid2)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->create();

        $this->graphQL($this->getQueryStr($user->id))
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
    public function get_period_today_by_accepted_at()
    {
        $userBuilder = $this->userBuilder();

        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $userBuilder->setUuid($userUuid)->create();
        $this->loginAsUser($user);

        $baseDate = CarbonImmutable::now();
        $date1 = $baseDate->addMinutes(10);
        $date2 = $baseDate->addDays(2);
        $date3 = $baseDate->subMinutes(10);

        $model = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date1)->create();
        $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date2)->create();
        $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date3)->create();

        $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_today))
            ->assertJson(["data" => [
                self::QUERY => [
                    "data" => [
                        ["id" => $model->id]
                    ],
                    "paginatorInfo" => [
                        "total" => 1,
                        "count" => 1
                    ]
                ]
            ]])
        ;
    }

    /** @test */
    public function get_period_today_by_accepted_at_another_user()
    {
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $userUuid_2 = "1ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $this->loginAsUser($user);

        $user_2 = $this->userBuilder()->setPhone('8908987978989')->setEmail('tt@tt.com')->setUuid($userUuid_2)->create();

        $baseDate = CarbonImmutable::now();
        $date1 = $baseDate->addMinutes(10);

        $model = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date1)->create();
        $this->agreementBuilder()->setUserUuid($userUuid_2)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date1)->create();

        $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_today))
            ->assertJson(["data" => [
                self::QUERY => [
                    "data" => [
                        ["id" => $model->id]
                    ],
                    "paginatorInfo" => [
                        "total" => 1,
                        "count" => 1
                    ]
                ]
            ]])
        ;
    }

    /** @test */
    public function get_period_today_by_accepted_at_few_model()
    {
        $userBuilder = $this->userBuilder();
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";

        $user = $userBuilder->setUuid($userUuid)->create();
        $this->loginAsUser($user);

        $baseDate = CarbonImmutable::now();
        $date1 = $baseDate->addMinutes(10);
        $date1_1 = $baseDate->addMinutes(60);
        $date2 = $baseDate->addDays(2);
        $date3 = $baseDate->subMinutes(10);

        $model_1 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date1)->create();
        $model_2 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date1_1)->create();
        $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date2)->create();
        $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date3)->create();
        $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt(null)->create();

        $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_today))
            ->assertJson(["data" => [
                self::QUERY => [
                    "data" => [
                        ["id" => $model_1->id],
                        ["id" => $model_2->id]
                    ],
                    "paginatorInfo" => [
                        "total" => 2,
                        "count" => 2
                    ]
                ]
            ]])
        ;
    }

    /** @test */
    public function get_period_today_by_accepted_at_different_status()
    {
        $userBuilder = $this->userBuilder();
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";

        $user = $userBuilder->setUuid($userUuid)->create();
        $this->loginAsUser($user);

        $baseDate = CarbonImmutable::now();
        $date1 = $baseDate->addMinutes(10);

        $model_1 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date1)->create();
        $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_NEW)->setAcceptedAt($date1)->create();
        $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_USED)->setAcceptedAt($date1)->create();
        $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_ERROR)->setAcceptedAt($date1)->create();

        $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_today))
            ->assertJson(["data" => [
                self::QUERY => [
                    "data" => [
                        ["id" => $model_1->id]
                    ],
                    "paginatorInfo" => [
                        "total" => 1,
                        "count" => 1
                    ]
                ]
            ]])
        ;
    }

    /** @test */
    public function get_period_incoming()
    {
        $userBuilder = $this->userBuilder();
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";

        $user = $userBuilder->setUuid($userUuid)->create();
        $this->loginAsUser($user);

        $baseDate = CarbonImmutable::now();
        $date1 = $baseDate->addMinutes(10);
        $date2 = $baseDate->addDays(2);
        $date3 = $baseDate->addDays(3);

        $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date1)->create();
        $model_1 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date2)->create();
        $model_2 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date3)->create();

        $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_incoming))
            ->assertJson(["data" => [
                self::QUERY => [
                    "data" => [
                        ["id" => $model_1->id],
                        ["id" => $model_2->id]
                    ],
                    "paginatorInfo" => [
                        "total" => 2,
                        "count" => 2
                    ]
                ]
            ]])
        ;
    }

    /** @test */
    public function get_period_incoming_with_sort_asc()
    {
        $userBuilder = $this->userBuilder();
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";

        $user = $userBuilder->setUuid($userUuid)->create();
        $this->loginAsUser($user);

        $baseDate = CarbonImmutable::now();
        $date1 = $baseDate->addDays(2);
        $date2 = $baseDate->addDays(3);
        $date3 = $baseDate->addDays(4);

        $model_1 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date1)->create();
        $model_2 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date2)->create();
        $model_3 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date3)->create();

        $this->graphQL($this->getQueryStrOrder($user->id, $this->order_period_incoming, 'ASC'))
            ->assertJson(["data" => [
                self::QUERY => [
                    "data" => [
                        ["id" => $model_1->id],
                        ["id" => $model_2->id],
                        ["id" => $model_3->id]
                    ],
                    "paginatorInfo" => [
                        "total" => 3,
                        "count" => 3
                    ]
                ]
            ]])
        ;
    }

    /** @test */
    public function get_period_incoming_with_sort_desc()
    {
        $userBuilder = $this->userBuilder();
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";

        $user = $userBuilder->setUuid($userUuid)->create();
        $this->loginAsUser($user);

        $baseDate = CarbonImmutable::now();
        $date1 = $baseDate->addDays(2);
        $date2 = $baseDate->addDays(3);
        $date3 = $baseDate->addDays(4);

        $model_1 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date1)->create();
        $model_2 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date2)->create();
        $model_3 = $this->agreementBuilder()->setUserUuid($userUuid)->setStatus(Agreement::STATUS_VERIFY)->setAcceptedAt($date3)->create();

        $this->graphQL($this->getQueryStrOrder($user->id, $this->order_period_incoming, 'DESC'))
            ->assertJson(["data" => [
                self::QUERY => [
                    "data" => [
                        ["id" => $model_3->id],
                        ["id" => $model_2->id],
                        ["id" => $model_1->id]
                    ],
                    "paginatorInfo" => [
                        "total" => 3,
                        "count" => 3
                    ]
                ]
            ]])
        ;
    }

    public function getQueryStr(string $id): string
    {
        return  sprintf('{
            %s (userId: %s) {
                data {
                    id
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
            self::QUERY,
            $id
        );
    }

    public static function getQueryStrPeriod($userId, $period): string
    {
        return  sprintf('{
            %s (userId: %s, period: %s) {
                data{
                    id
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            self::QUERY,
            $userId,
            $period
        );
    }

    public function getQueryStrOrder($userId, $period, string $sort = 'ASC'): string
    {
        return  sprintf('{
            %s (
                userId: %s,
                period: %s,
                orderBy: [{ field: ACCEPTED_AT, order: %s }]
            ) {
                data{
                    id
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            self::QUERY,
            $userId,
            $period,
            $sort
        );
    }
}
