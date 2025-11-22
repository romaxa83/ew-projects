<?php

namespace Tests\Feature\Api\GPS\Device;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
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
    }

    /** @test */
    public function success_list(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();
        $m_2 = $this->deviceBuilder->company($company)->create();
        $m_3 = $this->deviceBuilder->create();

        $this->getJson(route('gps.device-list-api'))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'to' => 2,
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_list_page(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();
        $m_2 = $this->deviceBuilder->company($company)->create();
        $m_3 = $this->deviceBuilder->create();

        $this->getJson(route('gps.device-list-api', [
            'page' => 2
        ]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'from' => null,
                    'last_page' => 1,
                    'to' => null,
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_list_per_page(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();
        $m_2 = $this->deviceBuilder->company($company)->create();
        $m_3 = $this->deviceBuilder->create();

        $this->getJson(route('gps.device-list-api', [
            'per_page' => 1
        ]))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 2,
                    'to' => 1,
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $this->getJson(route('gps.device-list-api'))
            ->assertJson([
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
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $m_1 Device */
        $m_1 = $this->deviceBuilder->company($company)->create();

        $truck = $this->truckBuilder->device($m_1)->create();

        $this->getJson(route('gps.device-list-api'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'name' => $m_1->name,
                        'imei' => $m_1->imei,
                        'status' => $m_1->status,
                        'phone' => $m_1->phone,
                        'company' => [
                            'id' => $company->id
                        ],
                        'force_deleted_at' => null,
                        'truck' => [
                            'id' => $truck->id,
                            'unit_number' => $truck->unit_number,
                        ],
                        'trailer' => null
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_list_check_data_trailer(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $m_1 Device */
        $m_1 = $this->deviceBuilder->company($company)->create();

        $trailer = $this->trailerBuilder->device($m_1)->create();

        $this->getJson(route('gps.device-list-api'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'name' => $m_1->name,
                        'imei' => $m_1->imei,
                        'status' => $m_1->status,
                        'phone' => $m_1->phone,
                        'company' => [
                            'id' => $company->id
                        ],
                        'force_deleted_at' => null,
                        'trailer' => [
                            'id' => $trailer->id,
                            'unit_number' => $trailer->unit_number,
                        ],
                        'truck' => null
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_list_default_sort(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $m_1 Device */
        $m_1 = $this->deviceBuilder->company($company)->name('AAA')
            ->status(DeviceStatus::ACTIVE())->create();
        $m_2 = $this->deviceBuilder->company($company)->name('BBB')
            ->status(DeviceStatus::INACTIVE())->create();
        $m_3 = $this->deviceBuilder->company($company)->name('DDDD')
            ->status(DeviceStatus::ACTIVE())->create();
        $m_4 = $this->deviceBuilder->company($company)->name('CCCC')
            ->status(DeviceStatus::DELETED())->create();

        $this->getJson(route('gps.device-list-api'))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                    ['id' => $m_4->id],
                    ['id' => $m_2->id],
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_status(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $m_1 Device */
        $m_1 = $this->deviceBuilder->company($company)->name('AAA')
            ->status(DeviceStatus::ACTIVE())->create();
        $m_2 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::INACTIVE())->create();
        $m_3 = $this->deviceBuilder->company($company)->name('BBB')
            ->status(DeviceStatus::ACTIVE())->create();
        $m_4 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::DELETED())->create();

        $this->getJson(route('gps.device-list-api', [
            'status' => DeviceStatus::ACTIVE
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 2
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_statuses(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $m_1 Device */
        $m_1 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())->create();
        $m_2 = $this->deviceBuilder->company($company)->name('BBB')
            ->status(DeviceStatus::INACTIVE())->create();
        $m_3 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())->create();
        $m_4 = $this->deviceBuilder->company($company)->name('AAA')
            ->status(DeviceStatus::DELETED())->create();

        $this->getJson(route('gps.device-list-api', [
            'statuses' => [DeviceStatus::INACTIVE, DeviceStatus::DELETED]
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_4->id],
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 2
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_has_history(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $m_1 Device */
        $m_1 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())->create();
        $m_2 = $this->deviceBuilder->company($company)->name('BBB')
            ->status(DeviceStatus::INACTIVE())->create();
        $m_3 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())->create();
        $m_4 = $this->deviceBuilder->company($company)->name('AAA')
            ->status(DeviceStatus::DELETED())->create();
        $m_5 = $this->deviceBuilder->company($company_2)->name('AAA')
            ->status(DeviceStatus::DELETED())->create();

        $h_1 = $this->historyBuilder->device($m_4)->create();
        $h_2 = $this->historyBuilder->device($m_4)->create();

        $h_3 = $this->historyBuilder->device($m_3)->create();
        $h_4 = $this->historyBuilder->device($m_5)->create();

        $this->getJson(route('gps.device-list-api', [
            'statuses' => [DeviceStatus::INACTIVE, DeviceStatus::DELETED],
            'has_history' => true
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_4->id],
                ],
                'meta' => [
                    'total' => 1
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_imei(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $m_1 Device */
        $m_1 = $this->deviceBuilder->company($company)->imei('Alex')
            ->status(DeviceStatus::ACTIVE())->create();
        $m_2 = $this->deviceBuilder->company($company)->imei('Den')
            ->status(DeviceStatus::INACTIVE())->create();
        $m_3 = $this->deviceBuilder->company($company)->imei('Ben')
            ->status(DeviceStatus::ACTIVE())->create();
        $m_4 = $this->deviceBuilder->company($company)->imei('Alexsis')
            ->status(DeviceStatus::DELETED())->create();

        $this->getJson(route('gps.device-list-api', [
            'query' => 'alex'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_4->id],
                ],
                'meta' => [
                    'total' => 2
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_phone_and_truck_unit_number(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $m_1 Device */
        $m_1 = $this->deviceBuilder->company($company)->phone('199997878')
            ->status(DeviceStatus::ACTIVE())->create();
        $m_2 = $this->deviceBuilder->company($company)->phone('199997872')
            ->status(DeviceStatus::INACTIVE())->create();
        $m_3 = $this->deviceBuilder->company($company)->phone('299997878')
            ->status(DeviceStatus::ACTIVE())->create();
        $m_4 = $this->deviceBuilder->company($company)->phone('399997878')
            ->status(DeviceStatus::DELETED())->create();
        $m_5 = $this->deviceBuilder->company($company)->phone('499997878')
            ->status(DeviceStatus::DELETED())->create();

        $truck = $this->truckBuilder->unitNumber('AA19999999')->device($m_5)->create();

        $this->getJson(route('gps.device-list-api', [
            'query' => '1999'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_5->id],
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 3
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_phone_and_trailer_unit_number(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $m_1 Device */
        $m_1 = $this->deviceBuilder->company($company)->phone('199997878')
            ->status(DeviceStatus::ACTIVE())->create();
        $m_2 = $this->deviceBuilder->company($company)->phone('199997872')
            ->status(DeviceStatus::INACTIVE())->create();
        $m_3 = $this->deviceBuilder->company($company)->phone('299997878')
            ->status(DeviceStatus::ACTIVE())->create();
        $m_4 = $this->deviceBuilder->company($company)->phone('399997878')
            ->status(DeviceStatus::DELETED())->create();
        $m_5 = $this->deviceBuilder->company($company)->phone('499997878')
            ->status(DeviceStatus::DELETED())->create();

        $trailer = $this->trailerBuilder->unitNumber('AA19999999')->device($m_5)->create();

        $this->getJson(route('gps.device-list-api', [
            'query' => '1999'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_5->id],
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 3
                ]
            ])
        ;
    }
}



