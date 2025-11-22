<?php


namespace Tests\Feature\Mutations\BackOffice\Vehicles;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Mutations\BackOffice\Vehicles\VehicleUpdateMutation;
use App\Models\Clients\Client;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Vehicle;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private VehicleClass $vehicleClass;
    private VehicleMake $vehicleMake;
    private SchemaVehicle $schemaVehicle;
    private Client $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();

        $this->vehicleClass = VehicleClass::factory()->addTypes()->create();
        $this->vehicleMake = VehicleMake::factory()->addModels()->create();
        $this->schemaVehicle = SchemaVehicle::factory()->create();
        $this->client = Client::factory()->create();
    }

    public function test_update_main_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleUpdateMutation::NAME)
                ->args([
                    'id' => $vehicle->id,
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
                    VehicleUpdateMutation::NAME => [
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
                    VehicleUpdateMutation::NAME => [
                        'id' => $vehicle->id,
                        'state_number' => $stateNumber,
                        'vin' => $vin,
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
    }

    public function test_update_trailer_vehicle(): void
    {
        $trailer = Vehicle::factory()->trailer()->create();
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleUpdateMutation::NAME)
                ->args([
                    'id' => $trailer->id,
                    'vehicle' => [
                        'state_number' => $stateNumber = $this->faker->stateNumber,
                        'vin' => $vin = $this->faker->vin,
                        'form' => VehicleFormEnum::TRAILER(),
                        'class_id' => $trailer->class_id,
                        'type_id' => $trailer->type_id,
                        'make_id' => $this->vehicleMake->id,
                        'model_id' => $this->vehicleMake->vehicleModels()->first()->id,
                        'client_id' => $this->client->id,
                        'schema_id' => $trailer->schema_id,
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
                    VehicleUpdateMutation::NAME => [
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
                    VehicleUpdateMutation::NAME => [
                        'id' => $trailer->id,
                        'created_at' => $trailer->created_at->getTimestamp(),
                        'updated_at' => $trailer->updated_at->getTimestamp(),
                        'state_number' => $stateNumber,
                        'vin' => $vin,
                        'form' => VehicleFormEnum::TRAILER,
                        'class' => [
                            'id' => $trailer->class_id,
                        ],
                        'type' => [
                            'id' => $trailer->type_id,
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
                            'id' => $trailer->schema_id,
                        ],
                        'odo' => $odo,
                        'active' => true
                    ]
                ]
            ]);
    }

    public function test_update_main_vehicle_with_null_odo(): void
    {
        $vehicle = Vehicle::factory()->create();
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VehicleUpdateMutation::NAME)
                ->args([
                    'id' => $vehicle->id,
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
                        'odo' => null,
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
                    VehicleUpdateMutation::NAME => [
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
                    VehicleUpdateMutation::NAME => [
                        'id' => $vehicle->id,
                        'created_at' => $vehicle->created_at->getTimestamp(),
                        'updated_at' => $vehicle->updated_at->getTimestamp(),
                        'state_number' => $stateNumber,
                        'vin' => $vin,
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
    }
}
