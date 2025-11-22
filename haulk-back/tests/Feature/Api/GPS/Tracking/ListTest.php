<?php

namespace Tests\Feature\Api\GPS\Tracking;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
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

class ListTest extends TestCase
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

        $this->passportInit();
    }

    /** @test */
    public function success_list_truck_and_trailer_have_one_driver(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $device = $this->deviceBuilder->company($company)->create();
        $device_2 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::DELETED())->create();

        $h_1 = $this->historyBuilder->create();
        $h_2 = $this->historyBuilder->create();
        $h_3 = $this->historyBuilder->create();

        $a_1 = $this->alertBuilder->history($h_2)->create();
        $a_2 = $this->alertBuilder->history($h_3)->create();
        $a_3 = $this->alertBuilder->history($h_3)->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->driver($user)
            ->lastDeviceHistory($h_2)
            ->lastDrivingAt($date->subMinutes(3))
            ->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder
            ->device($device_2)
            ->driver($user)
            ->lastDeviceHistory($h_3)
            ->lastDrivingAt($date->subMinutes(2))
            ->create();

        $this->getJson(route('gps.gps-tracking'))
            ->assertJson([
                'data' => [
                    [
                        'truck' => [
                            'id' => $truck->id,
                            'location' => [
                                'lat' => $h_2->latitude,
                                'lng' => $h_2->longitude,
                            ],
                            'event_type' => $h_2->event_type,
                            'vehicle_unit_number' => $truck->unit_number,
                            'driver_name' => $user->full_name,
                            'last_driving_at' => $date->subMinutes(3)->timestamp,
                            'alerts' => [
                                [
                                    'alert_type' => $a_1->alert_type,
                                    'details' => $a_1->getDetailsMessage(),
                                ]
                            ],
                        ],
                        'trailer' => [
                            'id' => $trailer->id,
                            'location' => [
                                'lat' => $h_3->latitude,
                                'lng' => $h_3->longitude,
                            ],
                            'event_type' => $h_3->event_type,
                            'vehicle_unit_number' => $trailer->unit_number,
                            'driver_name' => $user->full_name,
                            'last_driving_at' => $date->subMinutes(2)->timestamp,
                            'alerts' => [
                                [
                                    'alert_type' => $a_2->alert_type,
                                    'details' => $a_2->getDetailsMessage(),
                                ],
                                [
                                    'alert_type' => $a_3->alert_type,
                                    'details' => $a_3->getDetailsMessage(),
                                ]
                            ],
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.truck.alerts')
            ->assertJsonCount(2, 'data.0.trailer.alerts')
        ;
    }

    /** @test */
    public function success_list_truck_and_trailer_have_one_driver_no_data(): void
    {
        $this->loginAsCarrierSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $device = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::DELETED())
            ->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->driver($user)
            ->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder
            ->driver($user)
            ->create();

        $this->getJson(route('gps.gps-tracking'))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_list_truck_and_trailer_different_driver(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();
        $user_2 = $this->userBuilder->name('Wood Lack')->asDriver()
            ->company($company)->create();
        $user_3 = $this->userBuilder->name('Ben Ogest')->asDriver()
            ->company($company)->create();

        $device = $this->deviceBuilder->company($company)->status(DeviceStatus::DELETED())->create();
        $device_2 = $this->deviceBuilder->company($company)->create();
        $device_3 = $this->deviceBuilder->company($company)->create();
        $device_4 = $this->deviceBuilder->company($company)->create();

        $h_1 = $this->historyBuilder->create();
        $h_2 = $this->historyBuilder->create();
        $h_3 = $this->historyBuilder->create();
        $h_4 = $this->historyBuilder->create();
        $h_5 = $this->historyBuilder->create();
        $h_6 = $this->historyBuilder->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->lastDeviceHistory($h_1)
            ->lastDrivingAt($date->subMinutes(5))
            ->create();

        /** @var $truck_2 Truck */
        $truck_2 = $this->truckBuilder
            ->device($device_2)
            ->driver($user_2)
            ->lastDeviceHistory($h_2)
            ->lastDrivingAt($date->subMinutes(3))
            ->create();

        /** @var $truck_3 Truck */
        $truck_3 = $this->truckBuilder->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder
            ->device($device_3)
            ->driver($user_2)
            ->lastDeviceHistory($h_3)
            ->lastDrivingAt($date->subMinutes(2))
            ->create();

        /** @var $trailer_2 Trailer */
        $trailer_2 = $this->trailerBuilder
            ->device($device_4)
            ->driver($user_3)
            ->lastDeviceHistory($h_4)
            ->lastDrivingAt($date->subMinutes(4))
            ->create();


        $this->getJson(route('gps.gps-tracking'))
//            ->dump()
            ->assertJson([
                'data' => [
                    [
                        'truck' => null,
                        'trailer' => ['id' => $trailer_2->id],
                    ],
                    [
                        'truck' => ['id' => $truck_2->id,],
                        'trailer' => ['id' => $trailer->id,],

                    ],
                    [
                        'truck' => ['id' => $truck->id,],
                        'trailer' => null,
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_list_truck_and_trailer_sort_desc(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();
        $user_2 = $this->userBuilder->name('Wood Lack')->asDriver()
            ->company($company)->create();
        $user_3 = $this->userBuilder->name('Ben Ogest')->asDriver()
            ->company($company)->create();

        $device = $this->deviceBuilder->company($company)->create();
        $device_2 = $this->deviceBuilder->company($company)->create();
        $device_3 = $this->deviceBuilder->company($company)->create();
        $device_4 = $this->deviceBuilder->company($company)->create();

        $h_1 = $this->historyBuilder->create();
        $h_2 = $this->historyBuilder->create();
        $h_3 = $this->historyBuilder->create();
        $h_4 = $this->historyBuilder->create();
        $h_5 = $this->historyBuilder->create();
        $h_6 = $this->historyBuilder->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->lastDeviceHistory($h_1)
            ->lastDrivingAt($date->subMinutes(5))
            ->create();

        /** @var $truck_2 Truck */
        $truck_2 = $this->truckBuilder
            ->device($device_2)
            ->driver($user_2)
            ->lastDeviceHistory($h_2)
            ->lastDrivingAt($date->subMinutes(3))
            ->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder
            ->device($device_3)
            ->driver($user_3)
            ->lastDeviceHistory($h_3)
            ->lastDrivingAt($date->subMinutes(2))
            ->create();

        /** @var $trailer_2 Trailer */
        $trailer_2 = $this->trailerBuilder
            ->device($device_4)
            ->lastDeviceHistory($h_4)
            ->lastDrivingAt($date->subMinutes(4))
            ->create();


        $this->getJson(route('gps.gps-tracking', [
            'order_type' => 'desc'
        ]))
//            ->dump()
            ->assertJson([
                'data' => [
                    [
                        'truck' => ['id' => $truck_2->id],
                        'trailer' => null,
                    ],
                    [
                        'truck' => null,
                        'trailer' => ['id' =>  $trailer->id],
                    ],
                ]
            ])
            ->assertJsonCount(4, 'data')
        ;
    }

    /** @test */
    public function success_search_by_driver(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->name('Wood Allen')->asDriver()
            ->company($company)->create();
        $user_2 = $this->userBuilder->name('Ben Ogest')->asDriver()
            ->company($company)->create();
        $user_3 = $this->userBuilder->name('Wood Lack')->asDriver()
            ->company($company)->create();


        $device = $this->deviceBuilder->company($company)->create();
        $device_2 = $this->deviceBuilder->company($company)->create();
        $device_3 = $this->deviceBuilder->company($company)->create();

        $h_1 = $this->historyBuilder->create();
        $h_2 = $this->historyBuilder->create();
        $h_3 = $this->historyBuilder->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->driver($user)
            ->lastDeviceHistory($h_1)
            ->lastDrivingAt($date->subMinutes(5))
            ->create();

        /** @var $truck_2 Truck */
        $truck_2 = $this->truckBuilder
            ->device($device_2)
            ->driver($user_2)
            ->lastDeviceHistory($h_2)
            ->lastDrivingAt($date->subMinutes(3))
            ->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder
            ->device($device_3)
            ->driver($user_3)
            ->lastDeviceHistory($h_3)
            ->lastDrivingAt($date->subMinutes(2))
            ->create();

        $this->getJson(route('gps.gps-tracking', [
            'search' => 'wood'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'truck' => ['id' => $truck->id],
                    ],
                    [
                        'trailer' => ['id' => $trailer->id],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_search_by_unit_number(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->name('Wood Allen')->asDriver()
            ->company($company)->create();
        $user_2 = $this->userBuilder->name('Ben Ogest')->asDriver()
            ->company($company)->create();
        $user_3 = $this->userBuilder->name('Wood Lack')->asDriver()
            ->company($company)->create();


        $device = $this->deviceBuilder->company($company)->create();
        $device_2 = $this->deviceBuilder->company($company)->create();
        $device_3 = $this->deviceBuilder->company($company)->create();

        $h_1 = $this->historyBuilder->create();
        $h_2 = $this->historyBuilder->create();
        $h_3 = $this->historyBuilder->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->driver($user)
            ->lastDeviceHistory($h_1)
            ->lastDrivingAt($date->subMinutes(5))
            ->unitNumber('490000')
            ->create();

        /** @var $truck_2 Truck */
        $truck_2 = $this->truckBuilder
            ->device($device_2)
            ->driver($user_2)
            ->lastDeviceHistory($h_2)
            ->lastDrivingAt($date->subMinutes(3))
            ->unitNumber('490111')
            ->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder
            ->device($device_3)
            ->driver($user_3)
            ->lastDeviceHistory($h_3)
            ->lastDrivingAt($date->subMinutes(2))
            ->unitNumber('590000')
            ->create();

        $this->getJson(route('gps.gps-tracking', [
            'search' => '490'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'truck' => ['id' => $truck_2->id,]
                    ],
                    [
                        'truck' => ['id' => $truck->id,]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function not_get_if_truck_not_have_coords(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $device = $this->deviceBuilder->company($company)->create();
        $device_2 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::DELETED())->create();

        $h_1 = $this->historyBuilder->create();
        $h_2 = $this->historyBuilder->latitude(null)->longitude(null)->create();
        $h_3 = $this->historyBuilder->create();

        $a_1 = $this->alertBuilder->history($h_2)->create();
        $a_2 = $this->alertBuilder->history($h_3)->create();
        $a_3 = $this->alertBuilder->history($h_3)->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->driver($user)
            ->lastDeviceHistory($h_2)
            ->lastDrivingAt($date->subMinutes(3))
            ->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder
            ->device($device_2)
            ->driver($user)
            ->lastDeviceHistory($h_3)
            ->lastDrivingAt($date->subMinutes(2))
            ->create();

        $this->getJson(route('gps.gps-tracking'))
//            ->dump()
            ->assertJson([
                'data' => [
                    [
                        'truck' => [
                            'id' => $truck->id,
                            'location' => [
                                'lat' => null,
                                'lng' => null,
                            ],
                        ],
                        'trailer' => [
                            'id' => $trailer->id,
                            'location' => [
                                'lat' => $h_3->latitude,
                                'lng' => $h_3->longitude,
                            ],
                            'event_type' => $h_3->event_type,
                            'vehicle_unit_number' => $trailer->unit_number,
                            'driver_name' => $user->full_name,
                            'last_driving_at' => $date->subMinutes(2)->timestamp,
                            'alerts' => [
                                [
                                    'alert_type' => $a_2->alert_type,
                                    'details' => $a_2->getDetailsMessage(),
                                ],
                                [
                                    'alert_type' => $a_3->alert_type,
                                    'details' => $a_3->getDetailsMessage(),
                                ]
                            ],
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.trailer.alerts')
        ;
    }

    /** @test */
    public function not_get_if_trailer_not_have_coords(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();
        /** @var $user User */
        $user_2 = $this->userBuilder->asDriver()
            ->company($company)->create();

        $device = $this->deviceBuilder->company($company)->create();
        $device_2 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::DELETED())->create();

        $h_1 = $this->historyBuilder->create();
        $h_2 = $this->historyBuilder->latitude(null)->longitude(null)->create();
        $h_3 = $this->historyBuilder->create();

        $a_1 = $this->alertBuilder->history($h_2)->create();
        $a_2 = $this->alertBuilder->history($h_3)->create();
        $a_3 = $this->alertBuilder->history($h_3)->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->driver($user)
            ->lastDeviceHistory($h_3)
            ->lastDrivingAt($date->subMinutes(3))
            ->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder
            ->device($device_2)
            ->driver($user_2)
            ->lastDeviceHistory($h_2)
            ->lastDrivingAt($date->subMinutes(2))
            ->create();

        $this->getJson(route('gps.gps-tracking'))
            ->assertJson([
                'data' => [
                    [
                        'truck' => ['id' => $truck->id],
                        'trailer' => null
                    ],
                    [
                        'trailer' => ['id' => $trailer->id],
                        'truck' => null,
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_only_active(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();
        $user_2 = $this->userBuilder->name('Wood Lack')->asDriver()
            ->company($company)->create();
        $user_3 = $this->userBuilder->name('Ben Ogest')->asDriver()
            ->company($company)->create();

        $device = $this->deviceBuilder->company($company)->create();
        $device_2 = $this->deviceBuilder->company($company)->status(DeviceStatus::DELETED())->create();
        $device_3 = $this->deviceBuilder->company($company)->status(DeviceStatus::DELETED())->create();
        $device_4 = $this->deviceBuilder->company($company)->status(DeviceStatus::INACTIVE())->create();

        $h_1 = $this->historyBuilder->create();
        $h_2 = $this->historyBuilder->create();
        $h_3 = $this->historyBuilder->create();
        $h_4 = $this->historyBuilder->create();
        $h_5 = $this->historyBuilder->create();
        $h_6 = $this->historyBuilder->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->lastDeviceHistory($h_1)
            ->lastDrivingAt($date->subMinutes(5))
            ->create();

        /** @var $truck_2 Truck */
        $truck_2 = $this->truckBuilder
            ->device($device_2)
            ->driver($user_2)
            ->lastDeviceHistory($h_2)
            ->lastDrivingAt($date->subMinutes(3))
            ->create();

        /** @var $truck_3 Truck */
        $truck_3 = $this->truckBuilder->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder
            ->device($device_3)
            ->driver($user_2)
            ->lastDeviceHistory($h_3)
            ->lastDrivingAt($date->subMinutes(2))
            ->create();

        /** @var $trailer_2 Trailer */
        $trailer_2 = $this->trailerBuilder
            ->device($device_4)
            ->driver($user_3)
            ->lastDeviceHistory($h_4)
            ->lastDrivingAt($date->subMinutes(4))
            ->create();


        $this->getJson(route('gps.gps-tracking', ['device_statuses' => [DeviceStatus::ACTIVE]]))
//            ->dump()
            ->assertJson([
                'data' => [
                    [
                        'truck' => ['id' => $truck->id,],
                        'trailer' => null,
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_only_inactive(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();
        $user_2 = $this->userBuilder->name('Wood Lack')->asDriver()
            ->company($company)->create();
        $user_3 = $this->userBuilder->name('Ben Ogest')->asDriver()
            ->company($company)->create();

        $device = $this->deviceBuilder->company($company)->create();
        $device_2 = $this->deviceBuilder->company($company)->create();
        $device_3 = $this->deviceBuilder->company($company)->status(DeviceStatus::DELETED())->create();
        $device_4 = $this->deviceBuilder->company($company)->status(DeviceStatus::INACTIVE())->create();

        $h_1 = $this->historyBuilder->create();
        $h_2 = $this->historyBuilder->create();
        $h_3 = $this->historyBuilder->create();
        $h_4 = $this->historyBuilder->create();
        $h_5 = $this->historyBuilder->create();
        $h_6 = $this->historyBuilder->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder
            ->device($device)
            ->lastDeviceHistory($h_1)
            ->lastDrivingAt($date->subMinutes(5))
            ->create();

        /** @var $truck_2 Truck */
        $truck_2 = $this->truckBuilder
            ->device($device_2)
            ->driver($user_2)
            ->lastDeviceHistory($h_2)
            ->lastDrivingAt($date->subMinutes(3))
            ->create();

        /** @var $truck_3 Truck */
        $truck_3 = $this->truckBuilder->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder
            ->device($device_3)
            ->driver($user_2)
            ->lastDeviceHistory($h_3)
            ->lastDrivingAt($date->subMinutes(2))
            ->create();

        /** @var $trailer_2 Trailer */
        $trailer_2 = $this->trailerBuilder
            ->device($device_4)
            ->driver($user_3)
            ->lastDeviceHistory($h_4)
            ->lastDrivingAt($date->subMinutes(4))
            ->create();


        $this->getJson(route('gps.gps-tracking', ['device_statuses' => [
            DeviceStatus::INACTIVE,
            DeviceStatus::DELETED,
        ]]))
            ->assertJson([
                'data' => [
                    [
                        'truck' => null,
                        'trailer' => ['id' => $trailer_2->id],
                    ],
                    [
                        'truck' => null,
                        'trailer' => ['id' => $trailer->id,],

                    ],
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }
}


