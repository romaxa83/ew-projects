<?php

namespace Tests\Feature\Api\Statistics\First\V1;

use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\JD\ModelDescription;
use App\Models\Report\Feature\Feature;
use App\Models\User\Role;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ReportMachineTest extends TestCase
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

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();

        // FEATURES
        $val_1 = 'val_1';
        $val_2 = 'val_2';
        $val_2_2 = 'val_2_@';
        $feature_1 = $this->featureBuilder
            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
            ->setValues($val_1, $val_2)
            ->withTranslation()
            ->create();

        $feature_2 = $this->featureBuilder
            ->setValues($val_2_2)
            ->withTranslation()
            ->create();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                ["id" => $feature_1->id, "group" => [
                    ["choiceId" => $feature_1->values[0]->id]
                ]],
                ["id" => $feature_2->id, "group" => [
                    ["choiceId" => $feature_2->values[0]->id]
                ]]
            ])
            ->setModelDescription($md_1)->create();
        $rep_2 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)
            ->setFeatures([
                ["id" => $feature_1->id, "group" => [
                    ["choiceId" => $feature_1->values[0]->id]
                ]],
                ["id" => $feature_2->id, "group" => [
                    ["choiceId" => $feature_2->values[0]->id]
                ]]
            ])
            ->setModelDescription($md_1)->create();
        $rep_3 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealerId' => $dealer_1->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureResource([
                [
                    'id' => $rep_1->id,
                    "dealer" => [
                        "id" => $dealer_1->id,
                        "name" => $dealer_1->name
                    ],
                    "machine" => [
                        [
                            "equipment_group" => ["id" => $eg_1->id],
                            "model_description" => ["id" => $md_1->id],
                        ]
                    ],
                    "features" => [
                        [
                            "id" => $feature_1->id,
                            "group" => [
                                [
                                    "choiceId" => $feature_1->values[0]->id,
                                    "value" => $val_1,
                                ]
                            ]
                        ],
                        [
                            "id" => $feature_2->id,
                            "group" => [
                                [
                                    "choiceId" => $feature_2->values[0]->id,
                                    "value" => $val_2_2,
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'id' => $rep_2->id,
                    "dealer" => [
                        "id" => $dealer_1->id,
                        "name" => $dealer_1->name
                    ],
                    "machine" => [
                        [
                            "equipment_group" => ["id" => $eg_1->id],
                            "model_description" => ["id" => $md_1->id],
                        ]
                    ],
                    "features" => [
                        [
                            "id" => $feature_1->id,
                            "group" => [
                                [
                                    "choiceId" => $feature_1->values[0]->id,
                                    "value" => $val_1,
                                ]
                            ]
                        ],
                        [
                            "id" => $feature_2->id,
                            "group" => [
                                [
                                    "choiceId" => $feature_2->values[0]->id,
                                    "value" => $val_2_2,
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'id' => $rep_3->id,
                    "dealer" => [
                        "id" => $dealer_1->id,
                        "name" => $dealer_1->name
                    ],
                    "machine" => [
                        [
                            "equipment_group" => ["id" => $eg_1->id],
                            "model_description" => ["id" => $md_1->id],
                        ]
                    ],
                    "features" => []
                ]
            ]))
            ->assertJsonCount(3, 'data')
            ->assertJsonCount(2, 'data.0.features')
            ->assertJsonCount(2, 'data.1.features')
            ->assertJsonCount(0, 'data.2.features')
        ;
    }

    /** @test */
    public function success_few_field()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();


        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $rep_2 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $rep_3 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $rep_4 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson($this->structureResource([
                ['id' => $rep_1->id],
                ['id' => $rep_2->id],
                ['id' => $rep_3->id],
                ['id' => $rep_4->id],
            ]))
            ->assertJsonCount(4, 'data')
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

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();


        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setCreatedAt(Carbon::now()->subYear())->setModelDescription($md_1)->create();
        $rep_2 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $rep_3 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $rep_4 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->subYear()->year,
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson($this->structureResource([
                ['id' => $rep_1->id],
            ]))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_different_status()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();


        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $rep_2 = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $rep_3 = $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $rep_4 = $this->reportBuilder->setStatus(ReportStatus::EDITED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $rep_5 = $this->reportBuilder->setStatus(ReportStatus::VERIFY)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson($this->structureResource([
                ['id' => $rep_1->id],
                ['id' => $rep_4->id],
                ['id' => $rep_5->id],
            ]))
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();


        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson($this->structureResource([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_without_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson($this->structureErrorResponse(["The year field is required."]))
        ;
    }

    /** @test */
    public function fail_without_country()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson($this->structureErrorResponse(["The country field is required."]))
        ;
    }

    /** @test */
    public function fail_without_dealer()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson($this->structureErrorResponse(["The dealer id field is required."]))
        ;
    }

    /** @test */
    public function fail_without_eg()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson($this->structureErrorResponse(["The eg field is required."]))
        ;
    }

    /** @test */
    public function fail_without_md()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson($this->structureErrorResponse(["The md field is required."]))
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
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealerId' => $dealer_1->id.','.$dealer_2->id,
            'eg' => $eg_1->id,
            'md' => $md_1->id,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

