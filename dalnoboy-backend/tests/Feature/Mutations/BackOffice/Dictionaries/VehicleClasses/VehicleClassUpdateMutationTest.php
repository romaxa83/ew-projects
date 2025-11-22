<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\VehicleClasses;

use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses\VehicleClassUpdateMutation;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleClassTranslate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleClassUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_vehicle_class(): void
    {
        $vehicleClass = VehicleClass::factory()->create();

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

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleClassUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleClass->id,
                        'vehicle_class' => $vehicleClassData
                    ]
                )
                ->select(
                    [
                        'id',
                        'translates' => [
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk();

        $this->assertDatabaseHas(
            VehicleClass::class,
            [
                'id' => $vehicleClass->getKey(),
                'vehicle_form' => VehicleFormEnum::MAIN,
            ]
        );

        foreach ($translates as $translate) {
            $this->assertDatabaseHas(
                VehicleClassTranslate::class,
                [
                    'row_id' => $vehicleClass->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }

    public function test_update_vehicle_class_only_with_default_language(): void
    {
        $vehicleClass = VehicleClass::factory()->create();

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

        $vehicleClassData = [
            'active' => true,
            'vehicle_form' => VehicleFormEnum::MAIN(),
            'translations' => $translates,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleClassUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleClass->id,
                        'vehicle_class' => $vehicleClassData
                    ]
                )
                ->select(
                    [
                        'id',
                        'vehicle_form',
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
                VehicleClassTranslate::class,
                [
                    'row_id' => $vehicleClass->id,
                    'language' => (string) $translate['language'],
                    'title' => $translate['title'],
                ]
            );
        }
    }
    public function test_empty_default_language(): void
    {
        $vehicleClass = VehicleClass::factory()->create();

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
            GraphQLQuery::mutation(VehicleClassUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleClass->id,
                        'vehicle_class' => $vehicleClassData
                    ]
                )
                ->select(
                    [
                        'id',
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
                            'message' => 'validation'
                        ]
                    ]
                ]
            );
    }

    public function test_update_vehicle_class_without_vehicle_form(): void
    {
        $vehicleClass = VehicleClass::factory()->create();

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
            GraphQLQuery::mutation(VehicleClassUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $vehicleClass->id,
                        'vehicle_class' => $vehicleClassData
                    ]
                )
                ->select(
                    [
                        'id',
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
