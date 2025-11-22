<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleClasses;

use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses\VehicleClassCreateMutation;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleClassTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleClassCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_vehicle_class(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $vehicleClassData = [
            'active' => true,
            'vehicle_form' => VehicleFormEnum::MAIN(),
            'translations' => $translates,
        ];

        $vehicleClassId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleClassCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_class' => $vehicleClassData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'vehicle_form',
                        'translate' => [
                            'language',
                            'title',
                        ],
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        VehicleClassCreateMutation::NAME => [
                            'id',
                            'active',
                            'vehicle_form',
                            'translate' => [
                                'title',
                                'language'
                            ],
                            'translates' => [
                                '*' => [
                                    'title',
                                    'language'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . VehicleClassCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            VehicleClass::class,
            [
                'id' => $vehicleClassId,
                'vehicle_form' => VehicleFormEnum::MAIN,
            ]
        );

        $this->assertDatabaseHas(
            VehicleClassTranslate::class,
            [
                'row_id' => $vehicleClassId
            ]
        );
    }

    public function test_create_vehicle_class_only_with_default_language(): void
    {
        $title = $this->faker->text;
        $translates = [
            'language' => new EnumValue(defaultLanguage()->slug),
            'title' => $title,
        ];

        $vehicleClassData = [
            'active' => false,
            'vehicle_form' => VehicleFormEnum::MAIN(),
            'translations' => $translates,
        ];

        $vehicleClassId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleClassCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_class' => $vehicleClassData
                    ]
                )
                ->select(
                    [
                        'id',
                        'translate' => [
                            'language',
                            'title',
                        ],
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        VehicleClassCreateMutation::NAME => [
                            'id',
                            'translate' => [
                                'title',
                                'language'
                            ],
                            'translates' => [
                                '*' => [
                                    'title',
                                    'language'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . VehicleClassCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            VehicleClass::class,
            [
                'id' => $vehicleClassId
            ]
        );

        foreach (languages() as $language) {
            $this->assertDatabaseHas(
                VehicleClassTranslate::class,
                [
                    'row_id' => $vehicleClassId,
                    'language' => $language->slug,
                    'title' => $title,
                ]
            );
        }
    }

    public function test_empty_default_language(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            if ($language->default) {
                continue;
            }
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }

        $vehicleClassData = [
            'active' => true,
            'vehicle_form' => VehicleFormEnum::MAIN(),
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleClassCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_class' => $vehicleClassData
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'validation'
                        ]
                    ]
                ]
            );
    }

    public function test_create_vehicle_class_without_vehicle_form(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }
        $vehicleClassData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleClassCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_class' => $vehicleClassData
                    ]
                )
                ->select(
                    [
                        'id',
                        'vehicle_form',
                        'translate' => [
                            'language',
                            'title',
                        ],
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'Field VehicleClassInputType.vehicle_form of required type VehicleFormEnumType! was not provided.'
                        ]
                    ]
                ]
            );
    }
}
