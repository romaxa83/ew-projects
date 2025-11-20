<?php

namespace Tests\Feature\Api\Report\Create;

use App\Helpers\DateFormat;
use App\Models\JD\ModelDescription;
use App\Models\Report\Feature\Feature;
use App\Models\User\Role;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class WithFeatureTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success_create_only_choice_value()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        // FEATURES
        list($val_1, $val_2, $val_3, $val_4) = ['val_1', 'val_2', 'val_3', 'val_4'];
        /** @var $feature Feature */
        $feature_1 = $this->featureBuilder
            ->setValues($val_1, $val_2)
            ->withTranslation()
            ->create();
        $feature_2 = $this->featureBuilder
            ->setValues($val_3, $val_4)
            ->withTranslation()
            ->create();

        $this->assertEquals($feature_1->values[1]->current->name, $val_2);
        $this->assertEquals($feature_2->values[0]->current->name, $val_3);

        $data['features'] = [
            [
                'id' => $feature_1->id,
                'group' => [
                    ['choiceId' => $feature_1->values[1]->id],
                ]
            ],
            [
                'id' => $feature_2->id,
                'group' => [
                    ['choiceId' => $feature_2->values[0]->id],
                ]
            ]
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson([
                'data' => [
                    'fill_table_date' => DateFormat::front(Carbon::now()->toDateTimeString()),
                    'features' => [
                        [
                            "id" => $feature_1->id,
                            "name" => $feature_1->current->name,
                            "unit" => $feature_1->current->unit,
                            "type" => $feature_1->type,
                            "type_field" => $feature_1->type_field_for_front,
                            "is_sub" => false,
                            "group" => [
                                [
                                    "id" => null,
                                    "name" => null,
                                    "value" => $val_2,
                                    "choiceId" => $feature_1->values[1]->id,
                                ]
                            ]
                        ],
                        [
                            "id" => $feature_2->id,
                            "name" => $feature_2->current->name,
                            "unit" => $feature_2->current->unit,
                            "type" => $feature_2->type,
                            "type_field" => $feature_2->type_field_for_front,
                            "is_sub" => false,
                            "group" => [
                                [
                                    "id" => null,
                                    "name" => null,
                                    "value" => $val_3,
                                    "choiceId" => $feature_2->values[0]->id,
                                ]
                            ]
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.features')
        ;
    }

    /** @test */
    public function success_create_few_group()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        // FEATURES
        list($val_1, $val_2) = ['val_1', 'val_2'];
        /** @var $feature Feature */
        $feature_1 = $this->featureBuilder
            ->setValues($val_1, $val_2)
            ->withTranslation()->create();

        $data['features'] = [
            [
                'id' => $feature_1->id,
                'group' => [
                    ['choiceId' => $feature_1->values[0]->id],
                    ['choiceId' => $feature_1->values[1]->id],
                ]
            ],
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson([
                'data' => [
                    'fill_table_date' => DateFormat::front(Carbon::now()->toDateTimeString()),
                    'features' => [
                        [
                            "id" => $feature_1->id,
                            "name" => $feature_1->current->name,
                            "unit" => $feature_1->current->unit,
                            "type" => $feature_1->type,
                            "type_field" => $feature_1->type_field_for_front,
                            "is_sub" => false,
                            "group" => [
                                [
                                    "id" => null,
                                    "name" => null,
                                    "value" => $val_1,
                                    "choiceId" => $feature_1->values[0]->id,
                                ],
                                [
                                    "id" => null,
                                    "name" => null,
                                    "value" => $val_2,
                                    "choiceId" => $feature_1->values[1]->id,
                                ]
                            ]
                        ],
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data.features')
        ;
    }

    /** @test */
    public function success_create_only_custom_value()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        // FEATURES
        /** @var $feature Feature */
        $feature_1 = $this->featureBuilder->withTranslation()->create();
        $feature_2 = $this->featureBuilder->withTranslation()->create();

        $this->assertEmpty($feature_1->values);
        $this->assertEmpty($feature_2->values);

        list($val_1, $val_2) = ['val_1', 'val_2'];

        $data['features'] = [
            [
                'id' => $feature_1->id,
                'group' => [
                    ['value' => $val_1],
                ]
            ],
            [
                'id' => $feature_2->id,
                'group' => [
                    ['value' => $val_2],
                ]
            ]
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson([
                'data' => [
                    'fill_table_date' => DateFormat::front(Carbon::now()->toDateTimeString()),
                    'features' => [
                        [
                            "id" => $feature_1->id,
                            "name" => $feature_1->current->name,
                            "unit" => $feature_1->current->unit,
                            "type" => $feature_1->type,
                            "type_field" => $feature_1->type_field_for_front,
                            "is_sub" => false,
                            "group" => [
                                [
                                    "id" => null,
                                    "name" => null,
                                    "value" => $val_1,
                                    "choiceId" => null,
                                ]
                            ]
                        ],
                        [
                            "id" => $feature_2->id,
                            "name" => $feature_2->current->name,
                            "unit" => $feature_2->current->unit,
                            "type" => $feature_2->type,
                            "type_field" => $feature_2->type_field_for_front,
                            "is_sub" => false,
                            "group" => [
                                [
                                    "id" => null,
                                    "name" => null,
                                    "value" => $val_2,
                                    "choiceId" => null,
                                ]
                            ]
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.features')
        ;

        $feature_1->refresh();
        $feature_2->refresh();

        $this->assertEmpty($feature_1->values);
        $this->assertEmpty($feature_2->values);
    }

    /** @test */
    public function success_not_save_not_active_feature()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        // FEATURES
        /** @var $feature Feature */
        $feature_1 = $this->featureBuilder
            ->setActive(false)
            ->withTranslation()->create();

        $data['features'] = [
            [
                'id' => $feature_1->id,
                'group' => [
                    ['value' => 'some val'],
                ]
            ],
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson([
                'data' => [
                    'fill_table_date' => "",
                    'features' => [],
                ]
            ])
            ->assertJsonCount(0, 'data.features')
        ;
    }

    /** @test */
    public function success_create_with_md_data()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        $md_1 = ModelDescription::query()->first();
        $md_2 = ModelDescription::query()->where('id', '!=', $md_1->id)->first();

        // FEATURES
        /** @var $feature Feature */
        list($val_1, $val_2) = ['val_1', 'val_2'];
        $feature_1 = $this->featureBuilder
            ->withTranslation()->create();
        $feature_2 = $this->featureBuilder
            ->setValues($val_2)->withTranslation()->create();

        $this->assertEmpty($feature_1->values);
        $this->assertNotEmpty($feature_2->values);

        $data['features'] = [
            [
                'id' => $feature_1->id,
                'is_sub' => true,
                'group' => [
                    [
                        'id' => $md_1->id,
                        'name' => $md_1->name,
                        'value' => $val_1
                    ],
                ]
            ],
            [
                'id' => $feature_2->id,
                'is_sub' => false,
                'group' => [
                    [
                        'id' => $md_2->id,
                        'name' => $md_2->name,
                        'choiceId' => $feature_2->values[0]->id
                    ],
                ]
            ]
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson([
                'data' => [
                    'fill_table_date' => DateFormat::front(Carbon::now()->toDateTimeString()),
                    'features' => [
                        [
                            "id" => $feature_1->id,
                            "name" => $feature_1->current->name,
                            "unit" => $feature_1->current->unit,
                            "type" => $feature_1->type,
                            "type_field" => $feature_1->type_field_for_front,
                            "is_sub" => true,
                            "group" => [
                                [
                                    "id" => $md_1->id,
                                    "name" => $md_1->name,
                                    "value" => $val_1,
                                    "choiceId" => null,
                                ]
                            ]
                        ],
                        [
                            "id" => $feature_2->id,
                            "name" => $feature_2->current->name,
                            "unit" => $feature_2->current->unit,
                            "type" => $feature_2->type,
                            "type_field" => $feature_2->type_field_for_front,
                            "is_sub" => false,
                            "group" => [
                                [
                                    "id" => $md_2->id,
                                    "name" => $md_2->name,
                                    "value" => $val_2,
                                    "choiceId" => $feature_2->values[0]->id,
                                ]
                            ]
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.features')
        ;
    }

    /** @test */
    public function fail_wrong_md()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        // FEATURES
        /** @var $feature Feature */
        $val_1 = 'val_1';
        $feature_1 = $this->featureBuilder
            ->withTranslation()->create();

        $data['features'] = [
            [
                'id' => $feature_1->id,
                'is_sub' => true,
                'group' => [
                    [
                        'id' => 9999,
                        'name' => 'test',
                        'value' => $val_1
                    ],
                ]
            ]
        ];

        $this->postJson(route('api.report.create'), $data,[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureErrorResponse([
                "The selected features.0.group.0.id is invalid.",
                "The selected features.0.group.0.name is invalid."
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }
}



