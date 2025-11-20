<?php

namespace Tests\Feature\Api\Statistics\First\V1;

use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\JD\ModelDescription;
use App\Models\User\Role;
use App\Services\StatisticService;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ReportMachinesTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $reportBuilder;
    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();

        // FEATURES
        $feature_1 = $this->featureBuilder->withTranslation()->create();
        $feature_2 = $this->featureBuilder->withTranslation()->create();
        $feature_3 = $this->featureBuilder->withTranslation()->create();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 100,
                        ],
                    ]
                ],
                [
                    "id" => $feature_2->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 110,
                        ]
                    ]
                ],
                [
                    "id" => $feature_3->id,
                    "group" => [
                        [
                            "id" => $md_2->id,
                            "name" => $md_2->name,
                            "value" => 610,
                        ]
                    ]
                ]
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 130,
                        ]
                    ]
                ],
                [
                    "id" => $feature_2->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 100,
                        ]
                    ]
                ]
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 100,
                        ]
                    ]
                ],
                [
                    "id" => $feature_3->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 90,
                        ]
                    ]
                ]
            ])
            ->setModelDescription($md_1)->create();
        // not check
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_3)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 200,
                        ],
                    ]
                ],
            ])->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 250,
                        ],
                    ]
                ],
            ])->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_2->id,
                            "name" => $md_2->name,
                            "value" => 270,
                        ],
                    ]
                ],
            ])->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 70,
                        ],
                    ]
                ],
            ])->setModelDescription($md_2)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                [
                    "dealer" => $dealer_1->name,
                    "values" => [
                        [
                            "name" => $md_1->name,
                            "model_description_id" => $md_1->id,
                            "data" => [
                                [
                                    "value" => [
                                        "count" => 3,
                                        "avg" => (100 + 130 + 100) / 3
                                    ],
                                    "name" => $feature_1->current->name,
                                    "feature_id" => $feature_1->id,
                                ],
                                [
                                    "value" => [
                                        "count" => 2,
                                        "avg" => (110 + 100) / 2
                                    ],
                                    "name" => $feature_2->current->name,
                                    "feature_id" => $feature_2->id,
                                ],
                                [
                                    "value" => [
                                        "count" => 1,
                                        "avg" => 90
                                    ],
                                    "name" => $feature_3->current->name,
                                    "feature_id" => $feature_3->id,
                                ],
                            ]
                        ],

                    ]
                ],
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.values')
            ->assertJsonCount(3, 'data.0.values.0.data')
        ;
    }

    /** @test */
    public function success_some_fields()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();

        // FEATURES
        $feature_1 = $this->featureBuilder->withTranslation()->create();
        $feature_2 = $this->featureBuilder->withTranslation()->create();
        $feature_3 = $this->featureBuilder->withTranslation()->create();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];
        // first dealer
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 100,
                        ],
                    ]
                ],
                [
                    "id" => $feature_2->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 110,
                        ]
                    ]
                ],
                [
                    "id" => $feature_3->id,
                    "group" => [
                        [
                            "id" => $md_2->id,
                            "name" => $md_2->name,
                            "value" => 610,
                        ]
                    ]
                ]
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 130,
                        ]
                    ]
                ],
                [
                    "id" => $feature_2->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 100,
                        ]
                    ]
                ]
            ])
            ->setModelDescription($md_1)->create();
        // second dealer
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_3)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 100,
                        ]
                    ]
                ],
                [
                    "id" => $feature_2->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 590,
                        ]
                    ]
                ]
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_3)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 60,
                        ]
                    ]
                ],
                [
                    "id" => $feature_2->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 210,
                        ]
                    ]
                ]
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 200,
                        ],
                    ]
                ],
            ])->setModelDescription($md_1)->create();
        // not check
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_2->id,
                            "name" => $md_2->name,
                            "value" => 270,
                        ],
                    ]
                ],
            ])->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 70,
                        ],
                    ]
                ],
            ])->setModelDescription($md_2)->create();


        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                [
                    "dealer" => $dealer_1->name,
                    "values" => [
                        [
                            "name" => $md_1->name,
                            "model_description_id" => $md_1->id,
                            "data" => [
                                [
                                    "value" => [
                                        "count" => 2,
                                        "avg" => (100 + 130) / 2
                                    ],
                                    "name" => $feature_1->current->name,
                                    "feature_id" => $feature_1->id,
                                ],
                                [
                                    "value" => [
                                        "count" => 2,
                                        "avg" => (110 + 100) / 2
                                    ],
                                    "name" => $feature_2->current->name,
                                    "feature_id" => $feature_2->id,
                                ],
                            ]
                        ],

                    ]
                ],
                [
                    "dealer" => $dealer_2->name,
                    "values" => [
                        [
                            "name" => $md_1->name,
                            "model_description_id" => $md_1->id,
                            "data" => [
                                [
                                    "value" => [
                                        "count" => 3,
                                        "avg" => (100 + 60 + 200) / 3
                                    ],
                                    "name" => $feature_1->current->name,
                                    "feature_id" => $feature_1->id,
                                ],
                                [
                                    "value" => [
                                        "count" => 2,
                                        "avg" => (590 + 210) / 2
                                    ],
                                    "name" => $feature_2->current->name,
                                    "feature_id" => $feature_2->id,
                                ],
                            ]
                        ],

                    ]
                ],
            ]))
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(1, 'data.0.values')
            ->assertJsonCount(2, 'data.0.values.0.data')
            ->assertJsonCount(2, 'data.1.values.0.data')
        ;
    }

    /** @test */
    public function success_number_as_string_field()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        // FEATURES
        $feature_1 = $this->featureBuilder->withTranslation()->create();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => "100",
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => "120",
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => "140",
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                [
                    "dealer" => $dealer_1->name,
                    "values" => [
                        [
                            "name" => $md_1->name,
                            "model_description_id" => $md_1->id,
                            "data" => [
                                [
                                    "value" => [
                                        "count" => 3,
                                        "avg" => (100 + 120 + 140) /3
                                    ],
                                    "name" => $feature_1->current->name,
                                    "feature_id" => $feature_1->id,
                                ],
                            ]
                        ],

                    ]
                ],
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.values')
            ->assertJsonCount(1, 'data.0.values.0.data')
        ;
    }

    /** @test */
    public function success_strange_field_data()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        // FEATURES
        $feature_1 = $this->featureBuilder->withTranslation()->create();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => "70-90",
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 30,
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 140,
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                [
                    "dealer" => $dealer_1->name,
                    "values" => [
                        [
                            "name" => $md_1->name,
                            "model_description_id" => $md_1->id,
                            "data" => [
                                [
                                    "value" => [
                                        "count" => 3,
                                        "avg" => 83.33
                                    ],
                                    "name" => $feature_1->current->name,
                                    "feature_id" => $feature_1->id,
                                ],
                            ]
                        ],

                    ]
                ],
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.values')
            ->assertJsonCount(1, 'data.0.values.0.data')
        ;
    }

    /** @test */
    public function success_strange_field_data_2()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        // FEATURES
        $feature_1 = $this->featureBuilder->withTranslation()->create();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => "650/60R34",
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 30,
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 140,
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                [
                    "dealer" => $dealer_1->name,
                    "values" => [
                        [
                            "name" => $md_1->name,
                            "model_description_id" => $md_1->id,
                            "data" => [
                                [
                                    "value" => [
                                        "count" => 3,
                                        "avg" => 273.33
                                    ],
                                    "name" => $feature_1->current->name,
                                    "feature_id" => $feature_1->id,
                                ],
                            ]
                        ],

                    ]
                ],
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.values')
            ->assertJsonCount(1, 'data.0.values.0.data')
        ;
    }

    /** @test */
    public function success_last_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();

        // FEATURES
        $feature_1 = $this->featureBuilder->withTranslation()->create();
        $feature_2 = $this->featureBuilder->withTranslation()->create();
        $feature_3 = $this->featureBuilder->withTranslation()->create();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];
        // first dealer
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setCreatedAt(Carbon::now()->subYear())->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 100,
                        ],
                    ]
                ],
                [
                    "id" => $feature_2->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 110,
                        ]
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 130,
                        ]
                    ]
                ],
                [
                    "id" => $feature_2->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 100,
                        ]
                    ]
                ]
            ])
            ->setModelDescription($md_1)->create();
        // second dealer
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_3)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 100,
                        ]
                    ]
                ],
                [
                    "id" => $feature_2->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 590,
                        ]
                    ]
                ]
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_3)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 60,
                        ]
                    ]
                ],
                [
                    "id" => $feature_2->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 210,
                        ]
                    ]
                ]
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 200,
                        ],
                    ]
                ],
            ])->setModelDescription($md_1)->create();
        // not check
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_2->id,
                            "name" => $md_2->name,
                            "value" => 270,
                        ],
                    ]
                ],
            ])->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 70,
                        ],
                    ]
                ],
            ])->setModelDescription($md_2)->create();


        $data = [
            'year' => Carbon::now()->subYear()->year,
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                [
                    "dealer" => $dealer_1->name,
                    "values" => [
                        [
                            "name" => $md_1->name,
                            "model_description_id" => $md_1->id,
                            "data" => [
                                [
                                    "value" => [
                                        "count" => 1,
                                        "avg" => 100
                                    ],
                                    "name" => $feature_1->current->name,
                                    "feature_id" => $feature_1->id,
                                ],
                                [
                                    "value" => [
                                        "count" => 1,
                                        "avg" => 110
                                    ],
                                    "name" => $feature_2->current->name,
                                    "feature_id" => $feature_2->id,
                                ],
                            ]
                        ],

                    ]
                ],
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.values')
            ->assertJsonCount(2, 'data.0.values.0.data')
        ;
    }

    /** @test */
    public function success_different_status()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        // FEATURES
        $feature_1 = $this->featureBuilder->withTranslation()->create();
        $feature_2 = $this->featureBuilder->withTranslation()->create();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 100,
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::VERIFY)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 120,
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::EDITED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 140,
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 160,
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => 180,
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                [
                    "dealer" => $dealer_1->name,
                    "values" => [
                        [
                            "name" => $md_1->name,
                            "model_description_id" => $md_1->id,
                            "data" => [
                                [
                                    "value" => [
                                        "count" => 3,
                                        "avg" => (100 + 120 + 140) /3
                                    ],
                                    "name" => $feature_1->current->name,
                                    "feature_id" => $feature_1->id,
                                ],
                            ]
                        ],

                    ]
                ],
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.values')
            ->assertJsonCount(1, 'data.0.values.0.data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_string_field()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        // FEATURES
        $feature_1 = $this->featureBuilder->withTranslation()->create();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => "yes",
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => "no",
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                [
                    "id" => $feature_1->id,
                    "group" => [
                        [
                            "id" => $md_1->id,
                            "name" => $md_1->name,
                            "value" => "140",
                        ],
                    ]
                ],
            ])
            ->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse("Unsupported operand types: int + string"))
        ;
    }

    /** @test */
    public function fail_without_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse(["The year field is required."]))
        ;
    }

    /** @test */
    public function fail_without_country()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse(["The country field is required."]))
        ;
    }

    /** @test */
    public function fail_without_dealer()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse(["The dealer id field is required."]))
        ;
    }

    /** @test */
    public function fail_without_eg()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse(["The eg field is required."]))
        ;
    }

    /** @test */
    public function fail_without_md()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse(["The md field is required."]))
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->mock(StatisticService::class, function(MockInterface $mock){
            $mock->shouldReceive("statisticMachine")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $admin = $this->userBuilder
            ->setRole(Role::query()->where('role', Role::ROLE_PS)->first())
            ->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machines', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

