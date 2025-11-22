<?php

namespace Tests\Feature\Api\Vehicles\Trailers;

use App\Enums\Format\DateTimeEnum;
use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\History\HistoryBuilder as HistoryCommonBuilder;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\HistoryBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TrailerDriverHistoryBuilder;
use Tests\Helpers\Traits\AssertErrors;
use Tests\TestCase;

class TrailerAddDriverHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use AssertErrors;

    protected string $routeName = 'trailers.add-drivers-history';

    public array $connectionsToTransact = [
        DbConnections::DEFAULT, DbConnections::GPS
    ];

    protected CompanyBuilder $companyBuilder;
    protected HistoryBuilder $historyBuilder;
    protected HistoryCommonBuilder $historyCommonBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected TrailerDriverHistoryBuilder $trailerDriverHistoryBuilder;
    protected UserBuilder $userBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
        $this->historyCommonBuilder = resolve(HistoryCommonBuilder::class);
        $this->trailerDriverHistoryBuilder = resolve(TrailerDriverHistoryBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);

        $this->passportInit();
    }

    /** @test */
    public function success_add(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->create();
        $trailerAnother = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->startAt($date->subHours(19))
            ->create();

        $h_2 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailerAnother)
            ->startAt($date->subHours(10))
            ->endAt($date->subHours(8))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->assertEmpty($trailer->histories);

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $driverAnother->id
                        ],
                    ],
                    [
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ]
                ],
                'meta' => [
                    'total' => 2
                ],
            ])
        ;

        $trailer->refresh();

        $history = $trailer->histories[0];

        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->message, 'history.vehicle_updated');

    }

    /** @test */
    public function success_add_edit_rec_hit_start_at(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(30))
            ->endAt($date->subHours(29))
            ->create();

        $h_2 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(24))
            ->endAt($date->subHours(21))
            ->create();

        $h_3 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(10))
            ->endAt($date->subHours(8))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_3->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(10)->timestamp,
                        'unassigned_at' => $date->subHours(8)->timestamp,
                    ],
                    [
                        'id' => $h_2->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(30)->timestamp,
                        'unassigned_at' => $date->subHours(29)->timestamp,
                    ]
                ],
                'meta' => [
                    'total' => 3
                ],
            ])
        ;
    }

    /** @test */
    public function success_add_edit_rec_hit_end_at(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(30))
            ->endAt($date->subHours(29))
            ->create();

        $h_2 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(28))
            ->endAt($date->subHours(20))
            ->create();

        $h_3 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(10))
            ->endAt($date->subHours(8))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_3->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(10)->timestamp,
                        'unassigned_at' => $date->subHours(8)->timestamp,
                    ],
                    [
                        'id' => $h_2->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(30)->timestamp,
                        'unassigned_at' => $date->subHours(29)->timestamp,
                    ]
                ],
                'meta' => [
                    'total' => 3
                ],
            ])
        ;
    }

    /** @test */
    public function success_add_edit_rec_hit_end_at_but_not_equals(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(30))
            ->endAt($date->subHours(29))
            ->create();

        $h_2 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(28))
            ->endAt($date->subHours(22))
            ->create();

        $h_3 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(10))
            ->endAt($date->subHours(8))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_3->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(10)->timestamp,
                        'unassigned_at' => $date->subHours(8)->timestamp,
                    ],
                    [
                        'id' => $h_2->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(30)->timestamp,
                        'unassigned_at' => $date->subHours(29)->timestamp,
                    ]
                ],
                'meta' => [
                    'total' => 3
                ],
            ])
        ;
    }

    /** @test */
    public function success_add_edit_rec_into_period(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(30))
            ->endAt($date->subHours(29))
            ->create();

        $h_2 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(23))
            ->endAt($date->subHours(22))
            ->create();

        $h_3 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(10))
            ->endAt($date->subHours(8))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_3->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(10)->timestamp,
                        'unassigned_at' => $date->subHours(8)->timestamp,
                    ],
                    [
                        'id' => $h_2->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(30)->timestamp,
                        'unassigned_at' => $date->subHours(29)->timestamp,
                    ]
                ],
                'meta' => [
                    'total' => 3
                ],
            ])
        ;
    }

    /** @test */
    public function success_add_edit_rec_without_period(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(30))
            ->endAt($date->subHours(29))
            ->create();

        $h_2 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(25))
            ->endAt($date->subHours(19))
            ->create();

        $h_3 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailer)
            ->startAt($date->subHours(10))
            ->endAt($date->subHours(8))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(10)->timestamp,
                        'unassigned_at' => $date->subHours(8)->timestamp,
                    ],
                    [
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                    [
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(30)->timestamp,
                        'unassigned_at' => $date->subHours(29)->timestamp,
                    ]
                ],
                'meta' => [
                    'total' => 3
                ],
            ])
        ;
    }

    /** @test */
    public function success_add_check_gps_history(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());
        $user = $this->userBuilder->company($company)->create();
        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();

        $gps_h_1 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(26))
            ->create();
        $gps_h_2 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(25))
            ->create();
        $gps_h_3 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(23))
            ->create();
        $gps_h_4 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(22))
            ->create();
        $gps_h_5 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(19))
            ->create();

        $trailer->last_gps_history_id = $gps_h_5->id;
        $trailer->save();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertEquals($histories[0]->id, $gps_h_5->id);
        $this->assertEquals($histories[0]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[1]->id, $gps_h_4->id);
        $this->assertEquals($histories[1]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[2]->id, $gps_h_3->id);
        $this->assertEquals($histories[2]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[3]->id, $gps_h_2->id);
        $this->assertEquals($histories[3]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[4]->id, $gps_h_1->id);
        $this->assertEquals($histories[4]->driver_id, $driverAnother->id);

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'meta' => [
                    'total' => 1
                ],
            ])
        ;

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertCount(7, $histories);

        $this->assertEquals($histories[0]->id, $gps_h_5->id);
        $this->assertEquals($histories[0]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[1]->driver_id, $driverAnother->id);
        $this->assertEquals($histories[1]->old_driver_id, $driver->id);
        $this->assertEquals($histories[1]->event_type, History::EVENT_CHANGE_DRIVER);

        $this->assertEquals($histories[2]->id, $gps_h_4->id);
        $this->assertEquals($histories[2]->driver_id, $driver->id);

        $this->assertEquals($histories[3]->id, $gps_h_3->id);
        $this->assertEquals($histories[3]->driver_id, $driver->id);

        $this->assertEquals($histories[4]->driver_id, $driver->id);
        $this->assertEquals($histories[4]->old_driver_id, $driverAnother->id);
        $this->assertEquals($histories[4]->event_type, History::EVENT_CHANGE_DRIVER);

        $this->assertEquals($histories[5]->id, $gps_h_2->id);
        $this->assertEquals($histories[5]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[6]->id, $gps_h_1->id);
        $this->assertEquals($histories[6]->driver_id, $driverAnother->id);

        $trailer->refresh();

        $this->assertEquals($trailer->last_gps_history_id, $gps_h_5->id);
    }

    /** @test */
    public function success_add_check_gps_history_was_not_after_rec(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());
        $user = $this->userBuilder->company($company)->create();
        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();

        $gps_h_1 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(26))
            ->create();
        $gps_h_2 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(25))
            ->create();

        $trailer->last_gps_history_id = $gps_h_2->id;
        $trailer->save();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertEquals($histories[0]->id, $gps_h_2->id);
        $this->assertEquals($histories[0]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[1]->id, $gps_h_1->id);
        $this->assertEquals($histories[1]->driver_id, $driverAnother->id);

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'meta' => [
                    'total' => 1
                ],
            ])
        ;

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertCount(3, $histories);

        $this->assertEquals($histories[0]->driver_id, $driver->id);
        $this->assertEquals($histories[0]->old_driver_id, $driverAnother->id);
        $this->assertEquals($histories[0]->event_type, History::EVENT_CHANGE_DRIVER);

        $this->assertEquals($histories[1]->id, $gps_h_2->id);
        $this->assertEquals($histories[1]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[2]->id, $gps_h_1->id);
        $this->assertEquals($histories[2]->driver_id, $driverAnother->id);

        $trailer->refresh();

        $this->assertEquals($trailer->last_gps_history_id, $histories[0]->id);
    }

    /** @test */
    public function success_add_check_gps_history_was_not_after_rec_but_have_between(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());
        $user = $this->userBuilder->company($company)->create();
        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();

        $gps_h_1 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(26))
            ->create();
        $gps_h_2 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(25))
            ->create();
        $gps_h_3 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(23))
            ->create();

        $trailer->last_gps_history_id = $gps_h_3->id;
        $trailer->save();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertEquals($histories[0]->id, $gps_h_3->id);
        $this->assertEquals($histories[0]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[1]->id, $gps_h_2->id);
        $this->assertEquals($histories[1]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[2]->id, $gps_h_1->id);
        $this->assertEquals($histories[2]->driver_id, $driverAnother->id);

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'meta' => [
                    'total' => 1
                ],
            ])
        ;

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertCount(5, $histories);

        $this->assertEquals($histories[0]->driver_id, $driverAnother->id);
        $this->assertEquals($histories[0]->old_driver_id, $driver->id);
        $this->assertEquals($histories[0]->event_type, History::EVENT_CHANGE_DRIVER);

        $this->assertEquals($histories[1]->id, $gps_h_3->id);
        $this->assertEquals($histories[1]->driver_id, $driver->id);

        $this->assertEquals($histories[2]->driver_id, $driver->id);
        $this->assertEquals($histories[2]->old_driver_id, $driverAnother->id);
        $this->assertEquals($histories[2]->event_type, History::EVENT_CHANGE_DRIVER);

        $this->assertEquals($histories[3]->id, $gps_h_2->id);
        $this->assertEquals($histories[3]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[4]->id, $gps_h_1->id);
        $this->assertEquals($histories[4]->driver_id, $driverAnother->id);

        $trailer->refresh();

        $this->assertEquals($trailer->last_gps_history_id, $histories[0]->id);
    }

    /** @test */
    public function success_add_check_gps_history_was_not_before_rec(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());
        $user = $this->userBuilder->company($company)->create();
        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();

        $gps_h_5 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(19))
            ->create();

        $trailer->last_gps_history_id = $gps_h_5->id;
        $trailer->save();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertEquals($histories[0]->id, $gps_h_5->id);
        $this->assertEquals($histories[0]->driver_id, $driverAnother->id);

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'meta' => [
                    'total' => 1
                ],
            ])
        ;

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertCount(1, $histories);

        $this->assertEquals($histories[0]->id, $gps_h_5->id);
        $this->assertEquals($histories[0]->driver_id, $driverAnother->id);

        $trailer->refresh();

        $this->assertEquals($trailer->last_gps_history_id, $gps_h_5->id);
    }

    /** @test */
    public function success_add_check_gps_history_was_not_before_rec_by_have_between(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());
        $user = $this->userBuilder->company($company)->create();
        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();

        $gps_h_4 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(22))
            ->create();
        $gps_h_5 = $this->historyBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->receivedAt($date->subHours(19))
            ->create();

        $trailer->last_gps_history_id = $gps_h_5->id;
        $trailer->save();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertEquals($histories[0]->id, $gps_h_5->id);
        $this->assertEquals($histories[0]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[1]->id, $gps_h_4->id);
        $this->assertEquals($histories[1]->driver_id, $driverAnother->id);

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'meta' => [
                    'total' => 1
                ],
            ])
        ;

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertCount(4, $histories);

        $this->assertEquals($histories[0]->id, $gps_h_5->id);
        $this->assertEquals($histories[0]->driver_id, $driverAnother->id);

        $this->assertEquals($histories[1]->driver_id, $driverAnother->id);
        $this->assertEquals($histories[1]->old_driver_id, $driver->id);
        $this->assertEquals($histories[1]->event_type, History::EVENT_CHANGE_DRIVER);

        $this->assertEquals($histories[2]->id, $gps_h_4->id);
        $this->assertEquals($histories[2]->driver_id, $driver->id);

        $this->assertEquals($histories[3]->driver_id, $driver->id);
        $this->assertEquals($histories[3]->old_driver_id, $driverAnother->id);
        $this->assertEquals($histories[3]->event_type, History::EVENT_CHANGE_DRIVER);

        $trailer->refresh();

        $this->assertEquals($trailer->last_gps_history_id, $gps_h_5->id);
    }

    /** @test */
    public function success_add_check_gps_history_not_driver(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());
        $user = $this->userBuilder->company($company)->create();
        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();

        $gps_h_1 = $this->historyBuilder
            ->trailer($trailer)
            ->receivedAt($date->subHours(26))
            ->create();
        $gps_h_2 = $this->historyBuilder
            ->trailer($trailer)
            ->receivedAt($date->subHours(25))
            ->create();
        $gps_h_3 = $this->historyBuilder
            ->trailer($trailer)
            ->receivedAt($date->subHours(23))
            ->create();
        $gps_h_4 = $this->historyBuilder
            ->trailer($trailer)
            ->receivedAt($date->subHours(22))
            ->create();
        $gps_h_5 = $this->historyBuilder
            ->trailer($trailer)
            ->receivedAt($date->subHours(19))
            ->create();

        $trailer->last_gps_history_id = $gps_h_5->id;
        $trailer->save();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertEquals($histories[0]->id, $gps_h_5->id);
        $this->assertNull($histories[0]->driver_id);

        $this->assertEquals($histories[1]->id, $gps_h_4->id);
        $this->assertNull($histories[1]->driver_id);

        $this->assertEquals($histories[2]->id, $gps_h_3->id);
        $this->assertNull($histories[2]->driver_id);

        $this->assertEquals($histories[3]->id, $gps_h_2->id);
        $this->assertNull($histories[3]->driver_id);

        $this->assertEquals($histories[4]->id, $gps_h_1->id);
        $this->assertNull($histories[4]->driver_id);

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'meta' => [
                    'total' => 1
                ],
            ])
        ;

        $histories = History::query()->orderByDesc('received_at')->get();

        $this->assertCount(7, $histories);

        $this->assertEquals($histories[0]->id, $gps_h_5->id);
        $this->assertNull($histories[0]->driver_id);

        $this->assertNull($histories[1]->driver_id);
        $this->assertEquals($histories[1]->old_driver_id, $driver->id);
        $this->assertEquals($histories[1]->event_type, History::EVENT_CHANGE_DRIVER);

        $this->assertEquals($histories[2]->id, $gps_h_4->id);
        $this->assertEquals($histories[2]->driver_id, $driver->id);

        $this->assertEquals($histories[3]->id, $gps_h_3->id);
        $this->assertEquals($histories[3]->driver_id, $driver->id);

        $this->assertEquals($histories[4]->driver_id, $driver->id);
        $this->assertNull($histories[4]->old_driver_id);
        $this->assertEquals($histories[4]->event_type, History::EVENT_CHANGE_DRIVER);

        $this->assertEquals($histories[5]->id, $gps_h_2->id);
        $this->assertNull($histories[5]->driver_id);

        $this->assertEquals($histories[6]->id, $gps_h_1->id);
        $this->assertNull($histories[6]->driver_id);

        $trailer->refresh();

        $this->assertEquals($trailer->last_gps_history_id, $gps_h_5->id);
    }

    /** @test */
    public function success_add_check_gps_history_empty(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());
        $user = $this->userBuilder->company($company)->create();
        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->assertEmpty(History::query()->orderByDesc('received_at')->get());

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'meta' => [
                    'total' => 1
                ],
            ])
        ;

        $this->assertEmpty(History::query()->orderByDesc('received_at')->get());
    }

    // case-1
    /** @test */
    public function success_add_between_one_driver(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();
        $driverAnother_2 = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother_2)
            ->trailer($trailer)
            ->startAt($date->subHours(10))
            ->create();

        $h_2 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->startAt($date->subHours(30))
            ->endAt($date->subHours(10))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $driverAnother_2->id
                        ],
                        'assigned_at' => $date->subHours(10)->timestamp,
                        'unassigned_at' => null,
                    ],
                    [
                        'driver' => [
                            'id' => $driverAnother->id
                        ],
                        'assigned_at' => $date->subHours(20)->timestamp,
                        'unassigned_at' => $date->subHours(10)->timestamp,
                    ],
                    [
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                    [
                        'driver' => [
                            'id' => $driverAnother->id
                        ],
                        'assigned_at' => $date->subHours(30)->timestamp,
                        'unassigned_at' => $date->subHours(24)->timestamp,
                    ]
                ],
                'meta' => [
                    'total' => 4
                ],
            ])
        ;
    }

    // case-1
    /** @test */
    public function success_add_between_one_driver_who_not_has_unassigned_at(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->startAt($date->subHours(30))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'driver' => [
                            'id' => $driverAnother->id
                        ],
                        'assigned_at' => $date->subHours(20)->timestamp,
                        'unassigned_at' => null
                    ],
                    [
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                    [
                        'driver' => [
                            'id' => $driverAnother->id
                        ],
                        'assigned_at' => $date->subHours(30)->timestamp,
                        'unassigned_at' => $date->subHours(24)->timestamp,
                    ]
                ],
                'meta' => [
                    'total' => 3
                ],
            ])
        ;
    }

    // case-2
    /** @test */
    public function success_add_between_two_driver(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();
        $driverAnother_2 = $this->userBuilder->asDriver()->create();
        $driverAnother_3 = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother_3)
            ->trailer($trailer)
            ->startAt($date->subHours(10))
            ->create();

        $h_2 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother_2)
            ->trailer($trailer)
            ->startAt($date->subHours(30))
            ->endAt($date->subHours(22))
            ->create();

        $h_3 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->startAt($date->subHours(21))
            ->endAt($date->subHours(15))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $driverAnother_3->id
                        ],
                        'assigned_at' => $date->subHours(10)->timestamp,
                        'unassigned_at' => null,
                    ],
                    [
                        'id' => $h_3->id,
                        'driver' => [
                            'id' => $driverAnother->id
                        ],
                        'assigned_at' => $date->subHours(20)->timestamp,
                        'unassigned_at' => $date->subHours(15)->timestamp,
                    ],
                    [
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                    [
                        'id' => $h_2->id,
                        'driver' => [
                            'id' => $driverAnother_2->id
                        ],
                        'assigned_at' => $date->subHours(30)->timestamp,
                        'unassigned_at' => $date->subHours(24)->timestamp,
                    ]
                ],
                'meta' => [
                    'total' => 4
                ],
            ])
        ;
    }

    // case-3
    /** @test */
    public function success_add_has_another_driver_before(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();
        $driverAnother_2 = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother_2)
            ->trailer($trailer)
            ->startAt($date->subHours(10))
            ->create();

        $h_2 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->startAt($date->subHours(30))
            ->endAt($date->subHours(22))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $driverAnother_2->id
                        ],
                        'assigned_at' => $date->subHours(10)->timestamp,
                        'unassigned_at' => null,
                    ],
                    [
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                    [
                        'driver' => [
                            'id' => $driverAnother->id
                        ],
                        'assigned_at' => $date->subHours(30)->timestamp,
                        'unassigned_at' => $date->subHours(24)->timestamp,
                    ]
                ],
                'meta' => [
                    'total' => 3
                ],
            ])
        ;
    }

    // case-4
    /** @test */
    public function success_add_has_another_driver_after(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();
        $driverAnother_2 = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother_2)
            ->trailer($trailer)
            ->startAt($date->subHours(5))
            ->create();

        $h_2 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->startAt($date->subHours(22))
            ->endAt($date->subHours(10))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $driverAnother_2->id
                        ],
                        'assigned_at' => $date->subHours(5)->timestamp,
                        'unassigned_at' => null,
                    ],
                    [
                        'driver' => [
                            'id' => $driverAnother->id
                        ],
                        'assigned_at' => $date->subHours(20)->timestamp,
                        'unassigned_at' => $date->subHours(10)->timestamp,
                    ],
                    [
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                ],
                'meta' => [
                    'total' => 3
                ],
            ])
        ;
    }

    // case-4
    /** @test */
    public function success_add_has_another_driver_after_was_not_unassigned_at(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();
        $driverAnother = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driverAnother)
            ->trailer($trailer)
            ->startAt($date->subHours(22))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $data)
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $driverAnother->id
                        ],
                        'assigned_at' => $date->subHours(20)->timestamp,
                        'unassigned_at' => null,
                    ],
                    [
                        'driver' => [
                            'id' => $driver->id
                        ],
                        'assigned_at' => $date->subHours(24)->timestamp,
                        'unassigned_at' => $date->subHours(20)->timestamp,
                    ],
                ],
                'meta' => [
                    'total' => 2
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_driver(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->asDispatcher()->create();

        $data = [
            'driver_id' => $user->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $res = $this->postJson(route($this->routeName, $trailer->id), $data)
        ;

        $this->assertResponseHasValidationMessage($res, 'driver_id', __("exceptions.user.driver.not_driver"));
    }

    /** @test */
    public function fail_driver_was_assigned_another_truck(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();
        $trailerAnother = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailerAnother)
            ->startAt($date->subHours(30))
            ->endAt($date->subHours(15))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $res = $this->postJson(route($this->routeName, $trailer->id), $data)
        ;

        $this->assertResponseHasValidationMessage(
            $res,
            'start_at',
            __("exceptions.user.driver.history.driver_assigned_another_trailer", [
                'unit_number' => $trailerAnother->unit_number
            ])
        );
    }

    /** @test */
    public function fail_driver_was_assigned_another_truck_only_start_at(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();
        $trailerAnother = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailerAnother)
            ->startAt($date->subHours(30))
            ->endAt($date->subHours(23))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $res = $this->postJson(route($this->routeName, $trailer->id), $data)
        ;

        $this->assertResponseHasValidationMessage(
            $res,
            'start_at',
            __("exceptions.user.driver.history.driver_assigned_another_trailer", [
                'unit_number' => $trailerAnother->unit_number
            ])
        );
    }

    /** @test */
    public function fail_driver_was_assigned_another_truck_only_end_at(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();
        $trailerAnother = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailerAnother)
            ->startAt($date->subHours(23))
            ->endAt($date->subHours(19))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $res = $this->postJson(route($this->routeName, $trailer->id), $data)
        ;

        $this->assertResponseHasValidationMessage(
            $res,
            'start_at',
            __("exceptions.user.driver.history.driver_assigned_another_trailer", [
                'unit_number' => $trailerAnother->unit_number
            ])
        );
    }

    /** @test */
    public function fail_driver_was_assigned_another_truck_only_end_at_not_unassigned_at(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();
        $trailerAnother = $this->trailerBuilder->create();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $h_1 = $this->trailerDriverHistoryBuilder
            ->driver($driver)
            ->trailer($trailerAnother)
            ->startAt($date->subHours(23))
            ->create();

        $data = [
            'driver_id' => $driver->id,
            'start_at' => $date->subHours(24)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $res = $this->postJson(route($this->routeName, $trailer->id), $data)
        ;

        $this->assertResponseHasValidationMessage(
            $res,
            'start_at',
            __("exceptions.user.driver.history.driver_assigned_another_trailer", [
                'unit_number' => $trailerAnother->unit_number
            ])
        );
    }

    /** @test */
    public function fail_start_at_not_now(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $truck Truck */
        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->asDispatcher()->create();

        $data = [
            'driver_id' => $user->id,
            'start_at' => $date->subHours(2)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $res = $this->postJson(route($this->routeName, $trailer->id), $data)
        ;

        $this->assertResponseHasValidationMessage(
            $res,
            'start_at',
            trans('validation.custom.vehicle.driver_history.start_at', ['date' => $data['start_at']])
        );
    }

    /** @test */
    public function fail_start_at_more_than_3_days(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->asDispatcher()->create();

        $data = [
            'driver_id' => $user->id,
            'start_at' => $date->subDays(4)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $res = $this->postJson(route($this->routeName, $trailer->id), $data)
        ;

        $this->assertResponseHasValidationMessage(
            $res,
            'start_at',
            trans('validation.custom.vehicle.driver_history.start_at', ['date' => $data['start_at']])
        );
    }

    /** @test */
    public function fail_end_at_not_after_start_at(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $trailer $trailerTruck */
        $trailer = $this->trailerBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->asDispatcher()->create();

        $data = [
            'driver_id' => $user->id,
            'start_at' => $date->subHours(40)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->subHours(41)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $res = $this->postJson(route($this->routeName, $trailer->id), $data)
        ;

        $this->assertResponseHasValidationMessage(
            $res,
            'end_at',
            trans('validation.after', [
                'attribute' => 'end at',
                'date' => 'start at'
            ])
        );
    }

    /** @test */
    public function fail_end_at_in_a_future(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $truck Trailer */
        $trailer = $this->trailerBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->asDriver()->create();

        $data = [
            'driver_id' => $user->id,
            'start_at' => $date->subHours(21)->format(DateTimeEnum::DATE_TIME_FRONT),
            'end_at' => $date->addHours(20)->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $res = $this->postJson(route($this->routeName, $trailer->id), $data)
        ;

        $this->assertResponseHasValidationMessage(
            $res,
            'end_at',
            trans('validation.before', [
                'attribute' => 'end at',
                'date' => 'now'
            ])
        );
    }
}
