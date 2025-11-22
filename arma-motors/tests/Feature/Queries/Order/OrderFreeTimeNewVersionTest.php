<?php

namespace Tests\Feature\Queries\Order;

use App\Exceptions\ErrorsCode;
use App\Helpers\DateTime;
use App\Models\AA\AAOrder;
use App\Models\AA\AAPost;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Types\Order\Status;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\_Helpers\AAOrderBuilder;
use Tests\TestCase;
use Tests\Traits\Builders\AAPostBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class OrderFreeTimeNewVersionTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use AAPostBuilder;
    use OrderBuilder;

    protected $aaOrderBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->aaOrderBuilder = resolve(AAOrderBuilder::class);
    }

    /** @test */
    public function success()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $date = CarbonImmutable::now();
        /** @var $post_1 AAPost */
        $post_1 = $this->aaPostBuilder()->setSchedule([
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
        $post_2 = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->subDay(),
                'start_work' => $date->today()->subDay()->addHours(9),
                'end_work' => $date->today()->subDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        /** @var $order AAOrder */
        // первый пост, занято время с 9:00 по 11:30
        $order = $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->addHours(9),
            'end_date' => $date->today()->addHours(11)->addMinutes(30)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->addHours(9),
                'end_date' => $date->today()->addHours(11)->addMinutes(30)
            ]
        ])->create();
        // первый пост, занято время с 13:10 по 16:40
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->addHours(13)->addMinutes(10),
            'end_date' => $date->today()->addHours(16)->addMinutes(40)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->addHours(13)->addMinutes(10),
                'end_date' => $date->today()->addHours(16)->addMinutes(40)
            ]
        ])->create();
        // past
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
            'end_date' => $date->today()->subDay()->addHours(11)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
                'end_date' => $date->today()->subDay()->addHours(11)
            ]
        ])->create();
        // future
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
            'end_date' => $date->today()->subDay()->addHours(11)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
                'end_date' => $date->today()->subDay()->addHours(11)
            ]
        ])->create();

        // второй пост, занято время с 10:30 по 12:00
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_2->uuid,
            'start_date' => $date->today()->addHours(10)->addMinutes(30),
            'end_date' => $date->today()->addHours(12)
        ])->setPlanningData([
            [
                'post_uuid' => $post_2->uuid,
                'start_date' => $date->today()->addHours(10)->addMinutes(30),
                'end_date' => $date->today()->addHours(12)
            ]
        ])->create();
        // второй пост, занято время с 14:00 по 15:00
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_2->uuid,
            'start_date' => $date->today()->addHours(14),
            'end_date' => $date->today()->addHours(15)
        ])->setPlanningData([
            [
                'post_uuid' => $post_2->uuid,
                'start_date' => $date->today()->addHours(14),
                'end_date' => $date->today()->addHours(15)
            ]
        ])->create();

        // diagnostic
        $service = Service::find(7);
        // have timeStep
        $dealership = Dealership::find(1);

        $this->assertEquals(0, $service->time_step);

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)($date->today()->timestamp * 1000)
        ];

        $this->graphQL($this->getQueryStr($data))
            ->assertJson(["data" => [
                "orderFreeTime" => [
                    [
                       "postUuid" => $post_2->uuid,
                       "humanTime" => "09:00",
                       "milliseconds" => 32400000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "09:30",
                        "milliseconds" => 34200000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "10:00",
                        "milliseconds" => 36000000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "11:30",
                        "milliseconds" => 41400000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "12:00",
                        "milliseconds" => 43200000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "12:30",
                        "milliseconds" => 45000000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "13:00",
                        "milliseconds" => 46800000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "13:30",
                        "milliseconds" => 48600000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "15:00",
                        "milliseconds" => 54000000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "15:30",
                        "milliseconds" => 55800000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "16:00",
                        "milliseconds" => 57600000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "16:30",
                        "milliseconds" => 59400000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "17:00",
                        "milliseconds" => 61200000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "17:30",
                        "milliseconds" => 63000000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "18:00",
                        "milliseconds" => 64800000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "18:30",
                        "milliseconds" => 66600000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "19:00",
                        "milliseconds" => 68400000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "19:30",
                        "milliseconds" => 70200000,
                    ],
                ]
            ]])
            ->assertJsonCount(18, "data.orderFreeTime")
        ;
    }

    /** @test */
    public function success_step_one_hour()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $date = CarbonImmutable::now();
        /** @var $post_1 AAPost */
        $post_1 = $this->aaPostBuilder()->setSchedule([
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
        $post_2 = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->subDay(),
                'start_work' => $date->today()->subDay()->addHours(9),
                'end_work' => $date->today()->subDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        /** @var $order AAOrder */
        // первый пост, занято время с 9:00 по 11:30
        $order = $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->addHours(9),
            'end_date' => $date->today()->addHours(11)->addMinutes(30)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->addHours(9),
                'end_date' => $date->today()->addHours(11)->addMinutes(30)
            ]
        ])->create();
        // первый пост, занято время с 13:10 по 16:40
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->addHours(13)->addMinutes(10),
            'end_date' => $date->today()->addHours(16)->addMinutes(40)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->addHours(13)->addMinutes(10),
                'end_date' => $date->today()->addHours(16)->addMinutes(40)
            ]
        ])->create();
        // past
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
            'end_date' => $date->today()->subDay()->addHours(11)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
                'end_date' => $date->today()->subDay()->addHours(11)
            ]
        ])->create();
        // future
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
            'end_date' => $date->today()->subDay()->addHours(11)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
                'end_date' => $date->today()->subDay()->addHours(11)
            ]
        ])->create();

        // второй пост, занято время с 10:30 по 12:00
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_2->uuid,
            'start_date' => $date->today()->addHours(10)->addMinutes(30),
            'end_date' => $date->today()->addHours(12)
        ])->setPlanningData([
            [
                'post_uuid' => $post_2->uuid,
                'start_date' => $date->today()->addHours(10)->addMinutes(30),
                'end_date' => $date->today()->addHours(12)
            ]
        ])->create();
        // второй пост, занято время с 14:00 по 15:00
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_2->uuid,
            'start_date' => $date->today()->addHours(14),
            'end_date' => $date->today()->addHours(15)
        ])->setPlanningData([
            [
                'post_uuid' => $post_2->uuid,
                'start_date' => $date->today()->addHours(14),
                'end_date' => $date->today()->addHours(15)
            ]
        ])->create();

        // diagnostic
        $service = Service::find(7);
        $service->update(['time_step' => 60]);
        // have timeStep
        $dealership = Dealership::find(1);

        $this->assertEquals(60, $service->time_step);

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)($date->today()->timestamp * 1000)
        ];

        $this->graphQL($this->getQueryStr($data))
            ->assertJson(["data" => [
                "orderFreeTime" => [
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "09:00",
                        "milliseconds" => 32400000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "12:00",
                        "milliseconds" => 43200000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "13:00",
                        "milliseconds" => 46800000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "15:00",
                        "milliseconds" => 54000000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "16:00",
                        "milliseconds" => 57600000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "17:00",
                        "milliseconds" => 61200000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "18:00",
                        "milliseconds" => 64800000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "19:00",
                        "milliseconds" => 68400000,
                    ],
                ]
            ]])
            ->assertJsonCount(8, "data.orderFreeTime")
        ;
    }

    /** @test */
    public function success_with_order()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $date = CarbonImmutable::now();
        /** @var $post_1 AAPost */
        $post_1 = $this->aaPostBuilder()->setSchedule([
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
        $post_2 = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' =>true
            ],
            [
                'date' => $date->today()->subDay(),
                'start_work' => $date->today()->subDay()->addHours(9),
                'end_work' => $date->today()->subDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        /** @var $order AAOrder */
        // первый пост, занято время с 9:00 по 11:30
        $order = $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->addHours(9),
            'end_date' => $date->today()->addHours(11)->addMinutes(30)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->addHours(9),
                'end_date' => $date->today()->addHours(11)->addMinutes(30)
            ]
        ])->create();
        // первый пост, занято время с 13:10 по 16:40
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->addHours(13)->addMinutes(10),
            'end_date' => $date->today()->addHours(16)->addMinutes(40)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->addHours(13)->addMinutes(10),
                'end_date' => $date->today()->addHours(16)->addMinutes(40)
            ]
        ])->create();
        // past
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
            'end_date' => $date->today()->subDay()->addHours(11)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
                'end_date' => $date->today()->subDay()->addHours(11)
            ]
        ])->create();
        // future
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
            'end_date' => $date->today()->subDay()->addHours(11)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->subDay()->addHours(10)->addMinutes(30),
                'end_date' => $date->today()->subDay()->addHours(11)
            ]
        ])->create();

        // второй пост, занято время с 10:30 по 12:00
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_2->uuid,
            'start_date' => $date->today()->addHours(10)->addMinutes(30),
            'end_date' => $date->today()->addHours(12)
        ])->setPlanningData([
            [
                'post_uuid' => $post_2->uuid,
                'start_date' => $date->today()->addHours(10)->addMinutes(30),
                'end_date' => $date->today()->addHours(12)
            ]
        ])->create();
        // второй пост, занято время с 14:00 по 15:00
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_2->uuid,
            'start_date' => $date->today()->addHours(14),
            'end_date' => $date->today()->addHours(15)
        ])->setPlanningData([
            [
                'post_uuid' => $post_2->uuid,
                'start_date' => $date->today()->addHours(14),
                'end_date' => $date->today()->addHours(15)
            ]
        ])->create();

        // заявки на сегодня, время 12:00, пост 1
        $this->orderBuilder()
            ->setOnDate($date->today()->addHours(12))
            ->setPostUuid($post_1->uuid)
            ->asOne()->create();
        // заявки на сегодня, время 18:30, пост 1
        $this->orderBuilder()
            ->setOnDate($date->today()->addHours(18)->minutes(30))
            ->setPostUuid($post_1->uuid)
            ->asOne()->create();
        // заявки на сегодня, время 9:30, пост 2
        $this->orderBuilder()
            ->setOnDate($date->today()->addHours(9)->addMinutes(30))
            ->setPostUuid($post_2->uuid)
            ->asOne()->create();
        // заявки на вчера, время 10:00, пост 2 (не должна участвовать)
        $this->orderBuilder()
            ->setOnDate($date->today()->subDay()->addHours(10))
            ->setPostUuid($post_2->uuid)
            ->asOne()->create();

        // diagnostic
        $service = Service::find(7);
        // have timeStep
        $dealership = Dealership::find(1);

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)($date->today()->timestamp * 1000)
        ];

        $this->graphQL($this->getQueryStr($data))
            ->assertJson(["data" => [
                "orderFreeTime" => [
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "09:00",
                        "milliseconds" => 32400000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "10:00",
                        "milliseconds" => 36000000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "11:30",
                        "milliseconds" => 41400000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "12:00",
                        "milliseconds" => 43200000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "12:30",
                        "milliseconds" => 45000000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "13:00",
                        "milliseconds" => 46800000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "13:30",
                        "milliseconds" => 48600000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "15:00",
                        "milliseconds" => 54000000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "15:30",
                        "milliseconds" => 55800000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "16:00",
                        "milliseconds" => 57600000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "16:30",
                        "milliseconds" => 59400000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "17:00",
                        "milliseconds" => 61200000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "17:30",
                        "milliseconds" => 63000000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "18:00",
                        "milliseconds" => 64800000,
                    ],
                    [
                        "postUuid" => $post_2->uuid,
                        "humanTime" => "18:30",
                        "milliseconds" => 66600000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "19:00",
                        "milliseconds" => 68400000,
                    ],
                    [
                        "postUuid" => $post_1->uuid,
                        "humanTime" => "19:30",
                        "milliseconds" => 70200000,
                    ],
                ]
            ]])
            ->assertJsonCount(17, "data.orderFreeTime")
        ;
    }

    /** @test */
    public function success_not_work_day_to_schedule()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $date = CarbonImmutable::now();
        /** @var $post_1 AAPost */
        $post_1 = $this->aaPostBuilder()->setSchedule([
            [
                'date' => $date->today(),
                'start_work' => $date->today()->addHours(9),
                'end_work' => $date->today()->addHours(20),
                'work_day' => false
            ],
            [
                'date' => $date->today()->addDay(),
                'start_work' => $date->today()->addDay()->addHours(9),
                'end_work' => $date->today()->addDay()->addHours(20),
                'work_day' =>true
            ]
        ])->create();

        /** @var $order AAOrder */
        // первый пост, занято время с 9:00 по 11:30
        $order = $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->addHours(9),
            'end_date' => $date->today()->addHours(11)->addMinutes(30)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->addHours(9),
                'end_date' => $date->today()->addHours(11)->addMinutes(30)
            ]
        ])->create();
        // первый пост, занято время с 13:10 по 16:40
        $this->aaOrderBuilder->setData([
            'post_uuid' => $post_1->uuid,
            'start_date' => $date->today()->addHours(13)->addMinutes(10),
            'end_date' => $date->today()->addHours(16)->addMinutes(40)
        ])->setPlanningData([
            [
                'post_uuid' => $post_1->uuid,
                'start_date' => $date->today()->addHours(13)->addMinutes(10),
                'end_date' => $date->today()->addHours(16)->addMinutes(40)
            ]
        ])->create();

        // заявки на сегодня, время 12:00, пост 1
        $this->orderBuilder()
            ->setOnDate($date->today()->addHours(12))
            ->setPostUuid($post_1->uuid)
            ->asOne()->create();

        // diagnostic
        $service = Service::find(7);
        // have timeStep
        $dealership = Dealership::find(1);

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)($date->today()->timestamp * 1000)
        ];

        $this->graphQL($this->getQueryStr($data))
            ->assertJson(["data" => [
                "orderFreeTime" => []
            ]])
            ->assertJsonCount(0, "data.orderFreeTime")
        ;
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            {
                orderFreeTime(input:{
                    serviceId: %d
                    dealershipId: %d
                    date: "%s"
                }) {
                    postUuid
                    humanTime
                    milliseconds
                }
            }',
            $data['serviceId'],
            $data['dealershipId'],
            $data['date'],
        );
    }
}

