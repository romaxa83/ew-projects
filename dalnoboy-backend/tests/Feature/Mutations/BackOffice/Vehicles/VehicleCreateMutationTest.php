<?php


namespace Tests\Feature\Mutations\BackOffice\Vehicles;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Mutations\BackOffice\Vehicles\VehicleCreateMutation;
use App\Models\Clients\Client;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Vehicle;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private VehicleClass $vehicleClass;
    private VehicleMake $vehicleMake;
    private SchemaVehicle $schemaVehicle;
    private Client $client;
    private Vehicle $trailer;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();

        $this->vehicleClass = VehicleClass::factory()->addTypes()->create();
        $this->vehicleMake = VehicleMake::factory()->addModels()->create();
        $this->schemaVehicle = SchemaVehicle::factory()->create();
        $this->client = Client::factory()->create();
        $this->trailer = Vehicle::factory()->trailer()->create();
    }

    public function test_create_main_vehicle(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleCreateMutation::NAME)
                ->args([
                    'vehicle' => [
                        'state_number' => $stateNumber = $this->faker->stateNumber,
                        'vin' => $vin = $this->faker->vin,
                        'form' => VehicleFormEnum::MAIN(),
                        'class_id' => $this->vehicleClass->id,
                        'type_id' => $this->vehicleClass->vehicleTypes()->first()->id,
                        'make_id' => $this->vehicleMake->id,
                        'model_id' => $this->vehicleMake->vehicleModels()->first()->id,
                        'client_id' => $this->client->id,
                        'schema_id' => $this->schemaVehicle->id,
                        'odo' => $odo = $this->faker->odo,
                        'active' => true
                    ]
                ])
                ->select([
                    'id',
                    'created_at',
                    'updated_at',
                    'state_number',
                    'vin',
                    'is_moderated',
                    'form',
                    'class' => [
                        'id',
                    ],
                    'type' => [
                        'id',
                    ],
                    'make' => [
                        'id',
                    ],
                    'model' => [
                        'id',
                    ],
                    'client' => [
                        'id',
                        'manager' => [
                            'id',
                        ],
                    ],
                    'schema' => [
                        'id',
                    ],
                    'odo',
                    'active'
                ])
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    VehicleCreateMutation::NAME => [
                        'id',
                        'created_at',
                        'updated_at',
                        'state_number',
                        'vin',
                        'is_moderated',
                        'form',
                        'class' => [
                            'id',
                        ],
                        'type' => [
                            'id',
                        ],
                        'make' => [
                            'id',
                        ],
                        'model' => [
                            'id',
                        ],
                        'client' => [
                            'id',
                            'manager' => [
                                'id',
                            ],
                        ],
                        'schema' => [
                            'id',
                        ],
                        'odo',
                        'active'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    VehicleCreateMutation::NAME => [
                        'state_number' => $stateNumber,
                        'vin' => $vin,
                        'is_moderated' => true,
                        'form' => VehicleFormEnum::MAIN,
                        'class' => [
                            'id' => $this->vehicleClass->id,
                        ],
                        'type' => [
                            'id' => $this->vehicleClass->vehicleTypes()->first()->id,
                        ],
                        'make' => [
                            'id' => $this->vehicleMake->id,
                        ],
                        'model' => [
                            'id' => $this->vehicleMake->vehicleModels()->first()->id,
                        ],
                        'client' => [
                            'id' => $this->client->id,
                            'manager' => [
                                'id' => $this->client->manager_id,
                            ],
                        ],
                        'schema' => [
                            'id' => $this->schemaVehicle->id,
                        ],
                        'odo' => $odo,
                        'active' => true
                    ]
                ]
            ]);

        $this->assertDatabaseHas(
            Vehicle::class,
            [
                'vin' => $vin,
                'state_number' => $stateNumber
            ]
        );
    }

    public function test_create_trailer_vehicle(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleCreateMutation::NAME)
                ->args([
                    'vehicle' => [
                        'state_number' => $stateNumber = $this->faker->stateNumber,
                        'vin' => $vin = $this->faker->vin,
                        'form' => VehicleFormEnum::TRAILER(),
                        'class_id' => $this->trailer->class_id,
                        'type_id' => $this->trailer->type_id,
                        'make_id' => $this->vehicleMake->id,
                        'model_id' => $this->vehicleMake->vehicleModels()->first()->id,
                        'client_id' => $this->client->id,
                        'schema_id' => $this->trailer->schema_id,
                        'odo' => $odo = $this->faker->odo,
                        'active' => true
                    ]
                ])
                ->select([
                    'id',
                    'created_at',
                    'updated_at',
                    'state_number',
                    'vin',
                    'form',
                    'class' => [
                        'id',
                    ],
                    'type' => [
                        'id',
                    ],
                    'make' => [
                        'id',
                    ],
                    'model' => [
                        'id',
                    ],
                    'client' => [
                        'id',
                        'manager' => [
                            'id',
                        ],
                    ],
                    'schema' => [
                        'id',
                    ],
                    'odo',
                    'active'
                ])
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    VehicleCreateMutation::NAME => [
                        'id',
                        'created_at',
                        'updated_at',
                        'state_number',
                        'vin',
                        'form',
                        'class' => [
                            'id',
                        ],
                        'type' => [
                            'id',
                        ],
                        'make' => [
                            'id',
                        ],
                        'model' => [
                            'id',
                        ],
                        'client' => [
                            'id',
                            'manager' => [
                                'id',
                            ],
                        ],
                        'schema' => [
                            'id',
                        ],
                        'odo',
                        'active'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    VehicleCreateMutation::NAME => [
                        'state_number' => $stateNumber,
                        'vin' => $vin,
                        'form' => VehicleFormEnum::TRAILER,
                        'class' => [
                            'id' => $this->trailer->class_id,
                        ],
                        'type' => [
                            'id' => $this->trailer->type_id,
                        ],
                        'make' => [
                            'id' => $this->vehicleMake->id,
                        ],
                        'model' => [
                            'id' => $this->vehicleMake->vehicleModels()->first()->id,
                        ],
                        'client' => [
                            'id' => $this->client->id,
                            'manager' => [
                                'id' => $this->client->manager_id,
                            ],
                        ],
                        'schema' => [
                            'id' => $this->trailer->schema_id,
                        ],
                        'odo' => $odo,
                        'active' => true
                    ]
                ]
            ]);

        $this->assertDatabaseHas(
            Vehicle::class,
            [
                'vin' => $vin,
                'state_number' => $stateNumber
            ]
        );
    }

    public function test_try_to_create_main_vehicle_with_same_state_number(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleCreateMutation::NAME)
                ->args([
                    'vehicle' => [
                        'state_number' => $vehicle->state_number,
                        'vin' => $this->faker->vin,
                        'form' => VehicleFormEnum::MAIN(),
                        'class_id' => $this->vehicleClass->id,
                        'type_id' => $this->vehicleClass->vehicleTypes()->first()->id,
                        'make_id' => $this->vehicleMake->id,
                        'model_id' => $this->vehicleMake->vehicleModels()->first()->id,
                        'client_id' => $this->client->id,
                        'schema_id' => $this->schemaVehicle->id,
                        'odo' => $this->faker->odo,
                        'active' => true
                    ]
                ])
                ->select([
                    'id',
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'errors' => [
                    [
                        'message' => trans('validation.custom.vehicles.not_uniq_state_number')
                    ]
                ]
            ]);
    }

    public function test_try_to_create_main_vehicle_with_same_vin(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleCreateMutation::NAME)
                ->args([
                    'vehicle' => [
                        'state_number' => $this->faker->stateNumber,
                        'vin' => $vehicle->vin,
                        'form' => VehicleFormEnum::MAIN(),
                        'class_id' => $this->vehicleClass->id,
                        'type_id' => $this->vehicleClass->vehicleTypes()->first()->id,
                        'make_id' => $this->vehicleMake->id,
                        'model_id' => $this->vehicleMake->vehicleModels()->first()->id,
                        'client_id' => $this->client->id,
                        'schema_id' => $this->schemaVehicle->id,
                        'odo' => $this->faker->odo,
                        'active' => true
                    ]
                ])
                ->select([
                    'id',
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'errors' => [
                    [
                        'message' => trans('validation.custom.vehicles.not_uniq_vin')
                    ]
                ]
            ]);
    }

    public function test_try_to_create_main_vehicle_with_incorrect_form(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleCreateMutation::NAME)
                ->args([
                    'vehicle' => [
                        'state_number' => $this->faker->stateNumber,
                        'vin' => $this->faker->vin,
                        'form' => VehicleFormEnum::TRAILER(),
                        'class_id' => $this->vehicleClass->id,
                        'type_id' => $this->vehicleClass->vehicleTypes()->first()->id,
                        'make_id' => $this->vehicleMake->id,
                        'model_id' => $this->vehicleMake->vehicleModels()->first()->id,
                        'client_id' => $this->client->id,
                        'schema_id' => $this->schemaVehicle->id,
                        'odo' => $this->faker->odo,
                        'active' => true
                    ]
                ])
                ->select([
                    'id',
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'errors' => [
                    [
                        'message' => trans('validation.custom.vehicles.incorrect_vehicle_data')
                    ]
                ]
            ]);
    }

    public function test_is_moderated_for_admin_panel(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleCreateMutation::NAME)
                ->args([
                    'vehicle' => [
                        'state_number' => $stateNumber = $this->faker->stateNumber,
                        'vin' => $vin = $this->faker->vin,
                        'form' => VehicleFormEnum::MAIN(),
                        'class_id' => $this->vehicleClass->id,
                        'type_id' => $this->vehicleClass->vehicleTypes()->first()->id,
                        'make_id' => $this->vehicleMake->id,
                        'model_id' => $this->vehicleMake->vehicleModels()->first()->id,
                        'client_id' => $this->client->id,
                        'schema_id' => $this->schemaVehicle->id,
                        'odo' => $odo = $this->faker->odo,
                        'active' => true
                    ]
                ])
                ->select([
                    'id',
                    'is_moderated',
                ])
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    VehicleCreateMutation::NAME => [
                        'id',
                        'is_moderated',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    VehicleCreateMutation::NAME => [
                        'is_moderated' => true,
                    ]
                ]
            ]);
    }

    public function test_create_main_vehicle_without_odometer_in_back_office(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleCreateMutation::NAME)
                ->args([
                    'vehicle' => [
                        'state_number' => $stateNumber = $this->faker->stateNumber,
                        'vin' => $vin = $this->faker->vin,
                        'form' => VehicleFormEnum::MAIN(),
                        'class_id' => $this->vehicleClass->id,
                        'type_id' => $this->vehicleClass->vehicleTypes()->first()->id,
                        'make_id' => $this->vehicleMake->id,
                        'model_id' => $this->vehicleMake->vehicleModels()->first()->id,
                        'client_id' => $this->client->id,
                        'schema_id' => $this->schemaVehicle->id,
                        'active' => true
                    ]
                ])
                ->select([
                    'id',
                    'created_at',
                    'updated_at',
                    'state_number',
                    'vin',
                    'is_moderated',
                    'form',
                    'class' => [
                        'id',
                    ],
                    'type' => [
                        'id',
                    ],
                    'make' => [
                        'id',
                    ],
                    'model' => [
                        'id',
                    ],
                    'client' => [
                        'id',
                        'manager' => [
                            'id',
                        ],
                    ],
                    'schema' => [
                        'id',
                    ],
                    'odo',
                    'active'
                ])
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    VehicleCreateMutation::NAME => [
                        'id',
                        'created_at',
                        'updated_at',
                        'state_number',
                        'vin',
                        'is_moderated',
                        'form',
                        'class' => [
                            'id',
                        ],
                        'type' => [
                            'id',
                        ],
                        'make' => [
                            'id',
                        ],
                        'model' => [
                            'id',
                        ],
                        'client' => [
                            'id',
                            'manager' => [
                                'id',
                            ],
                        ],
                        'schema' => [
                            'id',
                        ],
                        'odo',
                        'active'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    VehicleCreateMutation::NAME => [
                        'state_number' => $stateNumber,
                        'vin' => $vin,
                        'is_moderated' => true,
                        'form' => VehicleFormEnum::MAIN,
                        'class' => [
                            'id' => $this->vehicleClass->id,
                        ],
                        'type' => [
                            'id' => $this->vehicleClass->vehicleTypes()->first()->id,
                        ],
                        'make' => [
                            'id' => $this->vehicleMake->id,
                        ],
                        'model' => [
                            'id' => $this->vehicleMake->vehicleModels()->first()->id,
                        ],
                        'client' => [
                            'id' => $this->client->id,
                            'manager' => [
                                'id' => $this->client->manager_id,
                            ],
                        ],
                        'schema' => [
                            'id' => $this->schemaVehicle->id,
                        ],
                        'odo' => null,
                        'active' => true
                    ]
                ]
            ]);

        $this->assertDatabaseHas(
            Vehicle::class,
            [
                'vin' => $vin,
                'state_number' => $stateNumber
            ]
        );
    }

    public function test_create_main_vehicle_without_odometer_in_front_office(): void
    {
        $this->loginAsUserWithRole();
        $this->postGraphQL(
            GraphQLQuery::mutation(VehicleCreateMutation::NAME)
                ->args([
                    'vehicle' => [
                        'state_number' => $this->faker->stateNumber,
                        'vin' => $this->faker->vin,
                        'form' => VehicleFormEnum::MAIN(),
                        'class_id' => $this->vehicleClass->id,
                        'type_id' => $this->vehicleClass->vehicleTypes()->first()->id,
                        'make_id' => $this->vehicleMake->id,
                        'model_id' => $this->vehicleMake->vehicleModels()->first()->id,
                        'client_id' => $this->client->id,
                        'schema_id' => $this->schemaVehicle->id,
                        'active' => true
                    ]
                ])
                ->select([
                    'id',
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'errors' => [
                    [
                        'message' => 'validation'
                    ]
                ]
            ]);
    }
}
