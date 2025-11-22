<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleTypes\VehicleTypeCreateMutation;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleType;
use App\Models\Dictionaries\VehicleTypeTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleTypeCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_vehicle_type(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }
        $vehicleClass1 = VehicleClass::factory()->create();
        $vehicleClass2 = VehicleClass::factory()->create();

        $vehicleTypeData = [
            'active' => true,
            'vehicle_classes' => [$vehicleClass1->getKey(), $vehicleClass2->getKey()],
            'translations' => $translates,
        ];

        $vehicleTypeId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleTypeCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_type' => $vehicleTypeData
                    ]
                )
                ->select(
                    [
                        'id',
                        'vehicle_classes' => [
                            'id',
                        ],
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
                        VehicleTypeCreateMutation::NAME => [
                            'id',
                            'vehicle_classes' => [
                                '*' => [
                                    'id',
                                ]
                            ],
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
            ->json('data.' . VehicleTypeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            VehicleType::class,
            [
                'id' => $vehicleTypeId,
            ]
        );

        $this->assertDatabaseHas(
            VehicleTypeTranslate::class,
            [
                'row_id' => $vehicleTypeId,
            ]
        );

        $this->assertDatabaseHas(
            'vehicle_class_vehicle_type',
            [
                'vehicle_class_id' => $vehicleClass1->getKey(),
                'vehicle_type_id' => $vehicleTypeId,
            ]
        );

        $this->assertDatabaseHas(
            'vehicle_class_vehicle_type',
            [
                'vehicle_class_id' => $vehicleClass2->getKey(),
                'vehicle_type_id' => $vehicleTypeId,
            ]
        );
    }

    public function test_create_vehicle_type_only_with_default_language(): void
    {
        $title = $this->faker->title;
        $translates = [
            'language' => new EnumValue(defaultLanguage()->slug),
            'title' => $title,
        ];

        $vehicleClass1 = VehicleClass::factory()->create();
        $vehicleClass2 = VehicleClass::factory()->create();

        $vehicleTypeData = [
            'active' => true,
            'vehicle_classes' => [$vehicleClass1->getKey(), $vehicleClass2->getKey()],
            'translations' => $translates,
        ];

        $vehicleTypeId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleTypeCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_type' => $vehicleTypeData
                    ]
                )
                ->select(
                    [
                        'id',
                        'vehicle_classes' => [
                            'id',
                        ],
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
                        VehicleTypeCreateMutation::NAME => [
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
            ->json('data.' . VehicleTypeCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            VehicleType::class,
            [
                'id' => $vehicleTypeId
            ]
        );

        foreach (languages() as $language) {
            $this->assertDatabaseHas(
                VehicleTypeTranslate::class,
                [
                    'row_id' => $vehicleTypeId,
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

        $vehicleClass1 = VehicleClass::factory()->create();
        $vehicleClass2 = VehicleClass::factory()->create();

        $vehicleTypeData = [
            'active' => true,
            'vehicle_classes' => [$vehicleClass1->getKey(), $vehicleClass2->getKey()],
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleTypeCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_type' => $vehicleTypeData
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

    public function test_create_vehicle_type_without_vehicle_class(): void
    {
        $translates = [];
        foreach (languages() as $language) {
            $translates[] = [
                'language' => new EnumValue($language->slug),
                'title' => $this->faker->text,
            ];
        }
        $vehicleTypeData = [
            'active' => true,
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleTypeCreateMutation::NAME)
                ->args(
                    [
                        'vehicle_type' => $vehicleTypeData
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
                            'message' => 'Field VehicleTypeInputType.vehicle_classes of required type [ID!]! was not provided.'
                        ]
                    ]
                ]
            );
    }
}
