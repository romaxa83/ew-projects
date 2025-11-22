<?php


namespace Tests\Feature\Mutations\BackOffice\Vehicles\Schemas;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Mutations\BackOffice\Vehicles\Schemas\SchemaVehicleUpdateMutation;
use App\Models\Vehicles\Schemas\SchemaAxle;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Schemas\SchemaWheel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SchemaVehicleUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_schema(): void
    {
        $schema = SchemaVehicle::factory()
            ->create();

        $wheels = $schema
            ->wheels
            ->random(mt_rand(1, $schema->wheels->count()))
            ->pluck('id')
            ->toArray();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SchemaVehicleUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $schema->id,
                        'schema' => [
                            'name' => $schema->name,
                            'original_schema_id' => $schema->id,
                            'wheels' => $wheels
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'name',
                        'vehicle_form',
                        'image',
                        'axles' => [
                            'id',
                            'position',
                            'name',
                            'wheels' => [
                                'id',
                                'position',
                                'name',
                                'use',
                                'updated_at',
                                'updated_at'
                            ],
                            'updated_at',
                            'updated_at',
                        ],
                        'updated_at',
                        'updated_at'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SchemaVehicleUpdateMutation::NAME => [
                            'id',
                            'name',
                            'vehicle_form',
                            'image',
                            'axles' => [
                                '*' => [
                                    'id',
                                    'position',
                                    'name',
                                    'wheels' => [
                                        '*' => [
                                            'id',
                                            'position',
                                            'name',
                                            'use',
                                            'updated_at',
                                            'updated_at'
                                        ]
                                    ],
                                    'updated_at',
                                    'updated_at',
                                ]
                            ],
                            'updated_at',
                            'updated_at'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        SchemaVehicleUpdateMutation::NAME => [
                            'name' => $schema->name,
                            'vehicle_form' => VehicleFormEnum::MAIN,
                            'axles' => $schema->axles->map(
                                fn(SchemaAxle $axle) => [
                                    'position' => $axle->position,
                                    'name' => $axle->name,
                                    'wheels' => $axle->wheels->map(
                                        fn(SchemaWheel $wheel) => [
                                            'position' => $wheel->position,
                                            'name' => $wheel->name,
                                            'use' => in_array($wheel->id, $wheels),
                                        ]
                                    )
                                        ->toArray()
                                ]
                            )
                                ->toArray(),
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_update_schema_with_the_similar_name(): void
    {
        $schemas = SchemaVehicle::factory()
            ->count(2)
            ->create();

        $wheels = $schemas[0]
            ->wheels
            ->random(mt_rand(1, $schemas[0]->wheels->count()))
            ->pluck('id')
            ->toArray();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SchemaVehicleUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $schemas[0]->id,
                        'schema' => [
                            'name' => $schemas[1]->name,
                            'original_schema_id' => $schemas[0]->id,
                            'wheels' => $wheels
                        ]
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
                            'message' => trans('validation.custom.vehicles.schemas.similar_schema_name')
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_update_schema_with_the_similar_list_of_wheel(): void
    {
        $schemas = SchemaVehicle::factory()
            ->count(2)
            ->create();

        $otherWheels = $schemas[1]
            ->wheels
            ->filter(
                fn(SchemaWheel $wheel) => $wheel->use
            )
            ->pluck('name')
            ->toArray();

        $wheels = $schemas[0]
            ->wheels
            ->filter(
                fn(SchemaWheel $wheel) => in_array($wheel->name, $otherWheels)
            )
            ->pluck('id')
            ->toArray();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SchemaVehicleUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $schemas[0]->id,
                        'schema' => [
                            'name' => $schemas[0]->name,
                            'original_schema_id' => $schemas[0]->id,
                            'wheels' => $wheels
                        ]
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
                            'message' => trans('validation.custom.vehicles.schemas.similar_schema')
                        ]
                    ]
                ]
            );
    }
}
