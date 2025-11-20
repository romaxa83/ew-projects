<?php

namespace Tests\Feature\Api\Statistics\V2;

use App\Models\JD\Dealer;
use App\Models\JD\ModelDescription;
use App\Models\User\Role;
use App\Services\Statistics\StatisticReportService;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ReportSizeTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $dealerFR_2 = Dealer::query()->where('country', $fr)->where('id', '!=', $dealerFR_1->id)->first();
        $this->assertNotNull($dealerFR_2);

        $de = "DE - German";
        $dealerDE = Dealer::query()->where('country', $de)->first();
        $this->assertNotNull($dealerDE);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_1_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_2 = $this->userBuilder->setDealer($dealerFR_2)
            ->setRole($role)->create();

        $userDE = $this->userBuilder->setDealer($dealerDE)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();
        $md_1_1 = ModelDescription::query()
            ->with('product')
            ->where([
                ['id', '!=', $md_1->id],
                ['eg_jd_id', $md_1->eg_jd_id],
            ])->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();
        $md_2 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->where('eg_jd_id', '!=', $md_1->eg_jd_id)
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1_1)
            ->setModelDescription($md_2)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userDE)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_2)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::VERIFY)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => [ReportStatus::CREATED, ReportStatus::VERIFY],
            'country' => [$fr, $de],
            'dealer' => [$dealerFR_1->id, $dealerFR_2->id, $dealerDE->id],
            'eg' => [$md_1->equipmentGroup->id, $md_2->equipmentGroup->id],
            'size' => [$md_1->product->id, $md_1_1->product->id, $md_2->product->id],
            'md' => [$md_1->id, $md_2->id, $md_1_1->id],
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureSuccessResponse([
                "countries" => [
                    [
                        "name" => "French",
                        "dealers_count" => 2
                    ],
                    [
                        "name" => "German",
                        "dealers_count" => 1
                    ]
                ],
                "data" => [
                    [
                        "name" => $md_1->name,
                        "size_name" => $md_1->product->size_name,
                        "id" => $md_1->id,
                        "eg" => [
                            "id" => $md_1->equipmentGroup->id,
                            "name" => $md_1->equipmentGroup->name,
                        ],
                        "values" => [
                            [
                                "dealer_name" => $dealerFR_1->name,
                                "value" => 2,
                            ],
                            [
                                "dealer_name" => $dealerFR_2->name,
                                "value" => 1,
                            ],
                            [
                                "dealer_name" => $dealerDE->name,
                                "value" => 1,
                            ]
                        ]
                    ],
                    [
                        "name" => $md_1_1->name,
                        "size_name" => $md_1_1->product->size_name,
                        "id" => $md_1_1->id,
                        "eg" => [
                            "id" => $md_1_1->equipmentGroup->id,
                            "name" => $md_1_1->equipmentGroup->name,
                        ],
                        "values" => [
                            [
                                "dealer_name" => $dealerFR_1->name,
                                "value" => 1,
                            ],
                            [
                                "dealer_name" => $dealerFR_2->name,
                                "value" => 0,
                            ],
                            [
                                "dealer_name" => $dealerDE->name,
                                "value" => 0,
                            ]
                        ]
                    ],
                    [
                        "name" => $md_2->name,
                        "size_name" => $md_2->product->size_name,
                        "id" => $md_2->id,
                        "eg" => [
                            "id" => $md_2->equipmentGroup->id,
                            "name" => $md_2->equipmentGroup->name,
                        ],
                        "values" => [
                            [
                                "dealer_name" => $dealerFR_1->name,
                                "value" => 1,
                            ],
                            [
                                "dealer_name" => $dealerFR_2->name,
                                "value" => 0,
                            ],
                            [
                                "dealer_name" => $dealerDE->name,
                                "value" => 0,
                            ]
                        ]
                    ]
                ]
            ]))
        ;
    }

    /** @test */
    public function success_fields_as_all()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $dealerFR_2 = Dealer::query()->where('country', $fr)->where('id', '!=', $dealerFR_1->id)->first();
        $this->assertNotNull($dealerFR_2);

        $de = "DE - German";
        $dealerDE = Dealer::query()->where('country', $de)->first();
        $this->assertNotNull($dealerDE);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_1_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_2 = $this->userBuilder->setDealer($dealerFR_2)
            ->setRole($role)->create();

        $userDE = $this->userBuilder->setDealer($dealerDE)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();
        $md_1_1 = ModelDescription::query()
            ->with('product')
            ->where([
                ['id', '!=', $md_1->id],
                ['eg_jd_id', $md_1->eg_jd_id],
            ])->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();
        $md_2 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->where('eg_jd_id', '!=', $md_1->eg_jd_id)
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1_1)
            ->setModelDescription($md_2)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userDE)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_2)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::VERIFY)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => 'all',
            'country' => 'all',
            'dealer' => 'all',
            'eg' => 'all',
            'size' => 'all',
            'md' => 'all',
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureSuccessResponse([
                "countries" => [
                    [
                        "name" => "French",
                        "dealers_count" => 2
                    ],
                    [
                        "name" => "German",
                        "dealers_count" => 1
                    ]
                ],
                "data" => [
                    [
                        "name" => $md_1->name,
                        "id" => $md_1->id,
                        "eg" => [
                            "id" => $md_1->equipmentGroup->id,
                            "name" => $md_1->equipmentGroup->name,
                        ],
                        "values" => [
                            [
                                "dealer_name" => $dealerFR_1->name,
                                "value" => 2,
                            ],
                            [
                                "dealer_name" => $dealerFR_2->name,
                                "value" => 1,
                            ],
                            [
                                "dealer_name" => $dealerDE->name,
                                "value" => 1,
                            ]
                        ]
                    ],
                    [
                        "name" => $md_1_1->name,
                        "id" => $md_1_1->id,
                        "eg" => [
                            "id" => $md_1_1->equipmentGroup->id,
                            "name" => $md_1_1->equipmentGroup->name,
                        ],
                        "values" => [
                            [
                                "dealer_name" => $dealerFR_1->name,
                                "value" => 1,
                            ],
                            [
                                "dealer_name" => $dealerFR_2->name,
                                "value" => 0,
                            ],
                            [
                                "dealer_name" => $dealerDE->name,
                                "value" => 0,
                            ]
                        ]
                    ],
                    [
                        "name" => $md_2->name,
                        "id" => $md_2->id,
                        "eg" => [
                            "id" => $md_2->equipmentGroup->id,
                            "name" => $md_2->equipmentGroup->name,
                        ],
                        "values" => [
                            [
                                "dealer_name" => $dealerFR_1->name,
                                "value" => 1,
                            ],
                            [
                                "dealer_name" => $dealerFR_2->name,
                                "value" => 0,
                            ],
                            [
                                "dealer_name" => $dealerDE->name,
                                "value" => 0,
                            ]
                        ]
                    ]
                ]
            ]))
        ;
    }

    /** @test */
    public function success_last_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setCreatedAt(Carbon::now()->subYear())
            ->setStatus(ReportStatus::CREATED)->create();

        // report not check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->subYear()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->equipmentGroup->id,
            'size' => $md_1->product->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureSuccessResponse([
                "countries" => [
                    [
                        "name" => "French",
                        "dealers_count" => 1
                    ],
                ],
                "data" => [
                    [
                        "name" => $md_1->name,
                        "id" => $md_1->id,
                        "eg" => [
                            "id" => $md_1->equipmentGroup->id,
                            "name" => $md_1->equipmentGroup->name,
                        ],
                        "values" => [
                            [
                                "dealer_name" => $dealerFR_1->name,
                                "value" => 1,
                            ],
                        ]
                    ],
                ]
            ]))
        ;
    }

    /** @test */
    public function fail_without_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
            'size' => $md_1->product->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureErrorResponse(['The year field is required.']))
        ;
    }

    /** @test */
    public function fail_without_status()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
            'size' => $md_1->product->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureErrorResponse(['The status field is required.']))
        ;
    }

    /** @test */
    public function fail_without_country()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
            'size' => $md_1->product->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureErrorResponse(['The country field is required.']))
        ;
    }

    /** @test */
    public function fail_without_dealer()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'eg' => $md_1->eg_jd_id,
            'size' => $md_1->product->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureErrorResponse(['The dealer field is required.']))
        ;
    }

    /** @test */
    public function fail_without_eg()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'size' => $md_1->product->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureErrorResponse(['The eg field is required.']))
        ;
    }

    /** @test */
    public function fail_without_size()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureErrorResponse(['The size field is required.']))
        ;
    }

    /** @test */
    public function fail_without_md()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
            'size' => $md_1->product->id,
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureErrorResponse(['The md field is required.']))
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
            'size' => $md_1->product->id,
            'md' => $md_1->id,
        ];

        $this->mock(StatisticReportService::class, function(MockInterface $mock){
            $mock->shouldReceive("reportSize")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $this->loginAsUser($userFR_1);

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
            'size' => $md_1->product->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->userBuilder->create();

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $md_1 = ModelDescription::query()
            ->with('product')
            ->whereHas('product', function($q){
                $q->whereNotNull('size_name');
            })
            ->first();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setModelDescription($md_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr,
            'dealer' => $dealerFR_1->id,
            'eg' => $md_1->eg_jd_id,
            'size' => $md_1->product->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.v2.statistic.size.report', $data))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
