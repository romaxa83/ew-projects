<?php

namespace Tests\Feature\Api\Report\Update\Ps;

use App\Helpers\DateFormat;
use App\Models\JD\ModelDescription;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UpdateFeatureTest extends TestCase
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
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where([['id', '!=', $md_1->id]])->first();
        $md_3 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['id', '!=', $md_2->id],
        ])->first();
        $data['machines'][] = [
            'model_description_id' => $md_1->id,
            'equipment_group_id' => $md_1->equipmentGroup->id,
        ];

        // FEATURES
        list($val_1, $val_2, $val_3, $val_4, $val_5) = ['val_1', 'val_2', 'val_3', 'val_4', 'val_5'];
        $feature_1 = $this->featureBuilder->setValues($val_1, $val_2)
            ->withTranslation()->setEgIds($md_1->equipmentGroup->id)->create();
        $feature_2 = $this->featureBuilder->setValues($val_3)
            ->withTranslation()->setEgIds($md_1->equipmentGroup->id)->create();
        $feature_3 = $this->featureBuilder->withTranslation()->setEgIds($md_1->equipmentGroup->id)->create();
        $feature_4 = $this->featureBuilder->withTranslation()->setEgIds($md_1->equipmentGroup->id)->create();

        $data['features'] = [
            [
                'id' => $feature_1->id,
                "is_sub" => true,
                'group' => [
                    [
                        'id' => $md_2->id,
                        'name' => $md_2->name,
                        'choiceId' => $feature_1->values[1]->id,
                    ],
                ]
            ],
            [
                'id' => $feature_2->id,
                "is_sub" => false,
                'group' => [
                    ['choiceId' => $feature_2->values[0]->id],
                ]
            ],
            [
                'id' => $feature_4->id,
                "is_sub" => true,
                'group' => [
                    [
                        'id' => $md_3->id,
                        'name' => $md_3->name,
                        'value' => $val_5
                    ],
                ]
            ]
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::OPEN_EDIT)
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature_1->id, "is_sub" => true, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "choiceId" => $feature_1->values[0]->id
                    ]
                ]],
                ["id" => $feature_3->id, "is_sub" =>  false, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "value" => $val_4,
                    ]
                ]]
            ])
            ->create();
        $rep->update(['fill_table_date' => null]);

        $this->assertCount(2, $rep->features);
        $this->assertNull($rep->fill_table_date);
        $this->assertNotNull($rep->result);

        $this->assertEquals($rep->features[0]->feature_id, $feature_1->id);
        $this->assertEquals($rep->features[0]->value->value_id, $feature_1->values[0]->id);
        $this->assertEquals($rep->features[0]->value->model_description_name, $md_1->name);
        $this->assertEquals($rep->features[0]->value->model_description_id, $md_1->id);
        $this->assertTrue($rep->features[0]->is_sub);

        $this->assertEquals($rep->features[1]->feature_id, $feature_3->id);
        $this->assertEquals($rep->features[1]->value->value, $val_4);
        $this->assertEquals($rep->features[1]->value->model_description_name, $md_1->name);
        $this->assertEquals($rep->features[1]->value->model_description_id, $md_1->id);
        $this->assertFalse($rep->features[1]->is_sub);

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
//            ->dump()
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    'fill_table_date' => DateFormat::front(CarbonImmutable::now()->toDateTimeString()),
                    "result" => null,
                    "features" => [
                        [
                            "id" => $feature_1->id,
                            "is_sub" => true,
                            "group" => [
                                [
                                    "id" => $md_2->id,
                                    "name" => $md_2->name,
                                    "value" => $val_2,
                                    "choiceId" => $feature_1->values[1]->id
                                ]
                            ]
                        ],
                        [
                            "id" => $feature_2->id,
                            "is_sub" => false,
                            "group" => [
                                [
                                    "id" => null,
                                    "name" => null,
                                    "value" => $val_3,
                                    "choiceId" => $feature_2->values[0]->id
                                ]
                            ]
                        ],
                        [
                            "id" => $feature_4->id,
                            "is_sub" => true,
                            "group" => [
                                [
                                    "id" => $md_3->id,
                                    "name" => $md_3->name,
                                    "value" => $val_5,
                                    "choiceId" => null,
                                ]
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(3, "data.features")
        ;
    }

    /** @test */
    public function success_not_edit_if_not_eg()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where([['id', '!=', $md_1->id]])->first();

        $data['machines'][] = [
            'model_description_id' => null,
            'equipment_group_id' => null,
        ];

        // FEATURES
        list($val_1, $val_2, $val_3) = ['val_1', 'val_2', 'val_3'];
        $feature_1 = $this->featureBuilder->setValues($val_1, $val_2)
            ->withTranslation()->setEgIds($md_1->equipmentGroup->id)->create();
        $feature_2 = $this->featureBuilder->setValues($val_3)
            ->withTranslation()->setEgIds($md_1->equipmentGroup->id)->create();

        $data['features'] = [
            [
                'id' => $feature_2->id,
                "is_sub" => true,
                'group' => [
                    [
                        'id' => $md_2->id,
                        'name' => $md_2->name,
                        'choiceId' => $feature_2->values[0]->id,
                    ],
                ]
            ],
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::OPEN_EDIT)
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature_1->id, "is_sub" => true, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "choiceId" => $feature_1->values[0]->id
                    ]
                ]],
            ])
            ->create();

        $this->assertCount(1, $rep->features);

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "features" => [
                        [
                            "id" => $feature_1->id,
                            "is_sub" => true,
                            "group" => [
                                [
                                    "id" => $md_1->id,
                                    "name" => $md_1->name,
//                                    "value" => null,
                                    "choiceId" => $feature_1->values[0]->id
                                ]
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, "data.features")
        ;
    }

    /** @test */
    public function success_remove_feature_if_eg_not_have_feature()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where([['id', '!=', $md_1->id]])->first();

        $data['machines'][] = [
            'model_description_id' => $md_1->id,
            'equipment_group_id' => $md_1->equipmentGroup->id,
        ];
        $data['result'] = 'result for update';

        // FEATURES
        list($val_1, $val_2, $val_3) = ['val_1', 'val_2', 'val_3'];
        $feature_1 = $this->featureBuilder->setValues($val_1, $val_2)
            ->withTranslation()->create();
        $feature_2 = $this->featureBuilder->setValues($val_3)
            ->withTranslation()->create();

        $data['features'] = [
            [
                'id' => $feature_2->id,
                "is_sub" => true,
                'group' => [
                    [
                        'id' => $md_2->id,
                        'name' => $md_2->name,
                        'choiceId' => $feature_2->values[0]->id,
                    ],
                ]
            ],
        ];

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::OPEN_EDIT)
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature_1->id, "is_sub" => true, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "choiceId" => $feature_1->values[0]->id
                    ]
                ]],
            ])
            ->create();

        $this->assertNotNull($rep->fill_table_date);
        $this->assertCount(1, $rep->features);
        $this->assertNotEquals($rep->result, $data['result']);

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "fill_table_date" => "",
                    "features" => [],
                    "result" => $data["result"],
                ]
            ])
            ->assertJsonCount(0, "data.features")
        ;
    }

    /** @test */
    public function success_remove_feature()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $md_1 = ModelDescription::query()->first();

        $data['machines'][] = [
            'model_description_id' => $md_1->id,
            'equipment_group_id' => $md_1->equipmentGroup->id,
        ];
        $data['result'] = 'result for update';

        // FEATURES
        list($val_1, $val_2) = ['val_1', 'val_2'];
        $feature_1 = $this->featureBuilder->setValues($val_1, $val_2)
            ->withTranslation()->setEgIds($md_1->equipmentGroup->id)->create();

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::OPEN_EDIT)
            ->setUser($user)
            ->setFeatures([
                ["id" => $feature_1->id, "is_sub" => true, "group" => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        "choiceId" => $feature_1->values[0]->id
                    ]
                ]],
            ])
            ->create();

        $this->assertNotNull($rep->fill_table_date);
        $this->assertNotEquals($rep->result, $data['result']);
        $this->assertCount(1, $rep->features);

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "fill_table_date" => "",
                    "result" => $data["result"],
                    "features" => []
                ]
            ])
            ->assertJsonCount(0, "data.features")
        ;
    }
}
