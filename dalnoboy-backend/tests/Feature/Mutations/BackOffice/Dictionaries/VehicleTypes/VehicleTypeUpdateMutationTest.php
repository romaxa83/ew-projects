<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleTypes;

use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleTypes\VehicleTypeUpdateMutation;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleType;
use App\Models\Dictionaries\VehicleTypeTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleTypeUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_vehicle_type(): void
    {
        $vehicleType = VehicleType::factory()->create();
        $vehicleClass = VehicleClass::factory()->create();
        $vehicleType->vehicleClasses()->attach($vehicleClass);

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

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleTypeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleType->id,
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
            ->assertOk();

        foreach ($translates as $translate) {
            $this->assertDatabaseHas(
                VehicleTypeTranslate::class,
                [
                    'row_id' => $vehicleType->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }

        $this->assertDatabaseHas(
            'vehicle_class_vehicle_type',
            [
                'vehicle_class_id' => $vehicleClass1->getKey(),
                'vehicle_type_id' => $vehicleType->getKey(),
            ]
        );

        $this->assertDatabaseHas(
            'vehicle_class_vehicle_type',
            [
                'vehicle_class_id' => $vehicleClass2->getKey(),
                'vehicle_type_id' => $vehicleType->getKey(),
            ]
        );

        $this->assertDatabaseMissing(
            'vehicle_class_vehicle_type',
            [
                'vehicle_class_id' => $vehicleClass->getKey(),
                'vehicle_type_id' => $vehicleType->getKey(),
            ]
        );
    }

    public function test_update_vehicle_type_only_with_default_language(): void
    {
        $vehicleType = VehicleType::factory()->create();

        $translates = [];
        foreach (languages() as $language) {
            if (!$language->default) {
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
            GraphQLQuery::mutation(VehicleTypeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleType->id,
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
            ->assertOk();

        foreach ($translates as $translate) {
            $this->assertDatabaseHas(
                VehicleTypeTranslate::class,
                [
                    'row_id' => $vehicleType->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }
    public function test_empty_default_language(): void
    {
        $vehicleType = VehicleType::factory()->create();

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
            GraphQLQuery::mutation(VehicleTypeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleType->id,
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

    public function test_update_vehicle_type_without_vehicle_classes(): void
    {
        $vehicleType = VehicleType::factory()->create();

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
            GraphQLQuery::mutation(VehicleTypeUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleType->id,
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
