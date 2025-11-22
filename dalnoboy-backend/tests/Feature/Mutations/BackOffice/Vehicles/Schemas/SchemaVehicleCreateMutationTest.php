<?php


namespace Tests\Feature\Mutations\BackOffice\Vehicles\Schemas;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Mutations\BackOffice\Vehicles\Schemas\SchemaVehicleCreateMutation;
use App\Models\Vehicles\Schemas\SchemaAxle;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Schemas\SchemaWheel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SchemaVehicleCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_create_schema(): void
    {
        $originalSchema = SchemaVehicle::default()
            ->vehicleForm(VehicleFormEnum::MAIN())
            ->with(['wheels'])
            ->first();

        $wheels = $originalSchema
            ->wheels
            ->random(mt_rand(1, $originalSchema->wheels->count()))
            ->pluck('id')
            ->toArray();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SchemaVehicleCreateMutation::NAME)
                ->args(
                    [
                        'schema' => [
                            'name' => $name = $this->faker->name,
                            'original_schema_id' => $originalSchema->id,
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
                                'created_at',
                                'updated_at'
                            ],
                            'created_at',
                            'updated_at',
                        ],
                        'created_at',
                        'updated_at'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SchemaVehicleCreateMutation::NAME => [
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
                                            'created_at',
                                            'updated_at'
                                        ]
                                    ],
                                    'created_at',
                                    'updated_at',
                                ]
                            ],
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        SchemaVehicleCreateMutation::NAME => [
                            'name' => $name,
                            'vehicle_form' => VehicleFormEnum::MAIN,
                            'axles' => $originalSchema->axles->map(
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

    public function test_try_to_create_schema_with_the_similar_name(): void
    {
        $originalSchema = SchemaVehicle::default()
            ->vehicleForm(VehicleFormEnum::MAIN())
            ->with(['wheels'])
            ->first();

        $schema = SchemaVehicle::factory()
            ->create();

        $wheels = $originalSchema
            ->wheels
            ->random(mt_rand(1, $originalSchema->wheels->count()))
            ->pluck('id')
            ->toArray();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SchemaVehicleCreateMutation::NAME)
                ->args(
                    [
                        'schema' => [
                            'name' => $schema->name,
                            'original_schema_id' => $originalSchema->id,
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

    public function test_try_to_create_schema_with_the_similar_list_of_wheel(): void
    {
        $originalSchema = SchemaVehicle::default()
            ->vehicleForm(VehicleFormEnum::MAIN())
            ->with(['wheels'])
            ->first();

        $schema = SchemaVehicle::factory()
            ->create();

        $similarWheels = $schema
            ->wheels
            ->filter(
                fn(SchemaWheel $similarWheel) => $similarWheel->use
            )
            ->values()
            ->pluck('name')
            ->toArray();

        $wheels = $originalSchema
            ->wheels
            ->filter(
                fn(SchemaWheel $wheel) => in_array($wheel->name, $similarWheels)
            )
            ->values()
            ->pluck('id')
            ->toArray();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SchemaVehicleCreateMutation::NAME)
                ->args(
                    [
                        'schema' => [
                            'name' => $this->faker->name,
                            'original_schema_id' => $originalSchema->id,
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
