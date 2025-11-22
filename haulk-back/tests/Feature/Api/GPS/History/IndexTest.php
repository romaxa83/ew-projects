<?php

namespace Tests\Feature\Api\GPS\History;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\AlertBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\HistoryBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected UserBuilder $userBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected HistoryBuilder $historyBuilder;
    protected AlertBuilder $alertBuilder;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
        $this->alertBuilder = resolve(AlertBuilder::class);

    }

    /** @test */
    public function success_paginator(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $h_1 = $this->historyBuilder->company($company)->receivedAt($date->subMinutes(10))->create();
        $h_2 = $this->historyBuilder->company($company)->receivedAt($date->subMinutes(9))->create();
        $h_3 = $this->historyBuilder->company($company)->receivedAt($date->subMinutes(8))->create();
        $h_4 = $this->historyBuilder->receivedAt($date->subMinutes(10))->create();

        $this->getJson(route('gps.gps-history-index'))
            ->assertJson([
                'data' => [
                    ['id' => $h_3->id],
                    ['id' => $h_2->id],
                    ['id' => $h_1->id],
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'to' => 3,
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_paginator_group_by_event(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $h_1 = $this->historyBuilder->company($company)
            ->eventType(History::EVENT_IDLE)
            ->receivedAt($date->subMinutes(20))->create();
        $h_2 = $this->historyBuilder->company($company)
            ->eventType(History::EVENT_IDLE)
            ->receivedAt($date->subMinutes(9))->create();
        $h_3 = $this->historyBuilder->company($company)
            ->receivedAt($date->subMinutes(8))
            ->eventType(History::EVENT_LONG_IDLE)
            ->create();
        $h_4 = $this->historyBuilder->company($company)
            ->receivedAt($date->subMinutes(7))
            ->eventType(History::EVENT_LONG_IDLE)
            ->create();
        $h_5 = $this->historyBuilder->company($company)
            ->receivedAt($date->subMinutes(10))
            ->eventType(History::EVENT_ENGINE_OFF)
            ->create();
        $h_6 = $this->historyBuilder->company($company)
            ->receivedAt($date->subMinutes(2))
            ->eventType(History::EVENT_ENGINE_OFF)
            ->create();

        $this->getJson(route('gps.gps-history-index'))
            ->assertJson([
                'data' => [
                    ['id' => $h_2->id],
                    ['id' => $h_1->id],
                    ['id' => $h_4->id],
                    ['id' => $h_3->id],
                    ['id' => $h_6->id],
                    ['id' => $h_5->id],
                ],
                'meta' => [
                    'total' => 6,
                ]
            ])
        ;
    }

    /** @test */
    public function success_paginator_per_page(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $h_1 = $this->historyBuilder->company($company)->receivedAt($date->subMinutes(10))->create();
        $h_2 = $this->historyBuilder->company($company)->receivedAt($date->subMinutes(9))->create();
        $h_3 = $this->historyBuilder->company($company)->receivedAt($date->subMinutes(8))->create();
        $h_4 = $this->historyBuilder->receivedAt($date->subMinutes(10))->create();

        $this->getJson(route('gps.gps-history-index', [
            'per_page' => 2
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $h_3->id],
                    ['id' => $h_2->id],
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 2,
                    'to' => 2,
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_paginator_page(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $h_1 = $this->historyBuilder->company($company)->receivedAt($date->subMinutes(10))->create();
        $h_2 = $this->historyBuilder->company($company)->receivedAt($date->subMinutes(9))->create();
        $h_3 = $this->historyBuilder->company($company)->receivedAt($date->subMinutes(8))->create();
        $h_4 = $this->historyBuilder->receivedAt($date->subMinutes(10))->create();

        $this->getJson(route('gps.gps-history-index', [
            'page' => 2
        ]))
            ->assertJson([
                'data' => [],
                'meta' => [
                    'current_page' => 2,
                    'from' => null,
                    'last_page' => 1,
                    'to' => null,
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $this->getJson(route('gps.gps-history-index'))
            ->assertJson([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'last_page' => 1,
                    'to' => null,
                    'total' => 0,
                ]
            ])
        ;
    }

    /** @test */
    public function success_list_check_data(): void
    {
        $date = CarbonImmutable::now();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $device = $this->deviceBuilder
            ->company($company)->create();
        $device_2 = $this->deviceBuilder
            ->status(DeviceStatus::DELETED())
            ->company($company)->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->driver($user)
            ->create();
        /** @var $truck Truck */
        $truck_1 = $this->truckBuilder
            ->device($device_2)
            ->driver($user)
            ->create();

        $h_1 = $this->historyBuilder->company($company)
            ->device($device)
            ->truck($truck)->create();

        $h_2 = $this->historyBuilder->company($company)
            ->device($device_2)
            ->truck($truck_1)
            ->create();
        $h_3 = $this->historyBuilder->create();

        $this->getJson(route('gps.gps-history-index'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_1->id,
                        'location' => [
                            'lat' => $h_1->latitude,
                            'lng' => $h_1->longitude,
                        ],
                        'speed' => $h_1->speed,
                        'vehicle_mileage' => $h_1->vehicle_mileage,
                        'unit_number' => $truck->unit_number,
                    ],
                    [
                        'id' => $h_2->id,
                        'location' => [
                            'lat' => $h_2->latitude,
                            'lng' => $h_2->longitude,
                        ],
                        'speed' => $h_2->speed,
                        'vehicle_mileage' => $h_2->vehicle_mileage,
                        'unit_number' => $truck_1->unit_number,
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_check_data_change_driver(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder
            ->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $device = $this->deviceBuilder
            ->company($company)->create();

        $driver = $this->userBuilder->asDriver()->create();
        $newDriver = $this->userBuilder->asDriver()->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->driver($newDriver)
            ->create();

        $h_1 = $this->historyBuilder
            ->company($company)
            ->device($device)
            ->truck($truck)
            ->driver($newDriver)
            ->oldDriver($driver)
            ->create();

        $this->getJson(route('gps.gps-history-index'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_1->id,
                        'driver' => [
                            'id' => $newDriver->id
                        ],
                        'old_driver' => [
                            'id' => $driver->id
                        ],
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_filter_alert_type(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $device = $this->deviceBuilder->company($company)->create();

        $driver = $this->userBuilder->asDriver()->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->create();

        $h_1 = $this->historyBuilder
            ->company($company)
            ->device($device)
            ->truck($truck)
            ->create();
        $h_2 = $this->historyBuilder
            ->company($company)
            ->device($device)
            ->truck($truck)
            ->create();
        $h_3 = $this->historyBuilder
            ->company($company)
            ->device($device)
            ->truck($truck)
            ->create();

        $alert_1 = $this->alertBuilder->history($h_3)->type('device_battery')->create();
        $alert_2 = $this->alertBuilder->history($h_2)->type('speeding')->create();

        $this->getJson(route('gps.gps-history-index', [
            'alert_type' => "speeding"
        ]))
//            ->dump()
            ->assertJson([
                'data' => [
                    [
                        'id' => $h_2->id,
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }
}
