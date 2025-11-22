<?php


namespace Tests\Feature\Queries\BackOffice\Vehicles;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Queries\Common\Vehicles\BaseVehiclesQuery;
use App\Models\Inspections\Inspection;
use App\Models\Vehicles\Vehicle;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehiclesQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $vehicles;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();

        Vehicle::factory()->count(5)->create();
        Vehicle::factory()->trailer()->count(5)->create();
        Vehicle::factory(['active' => false])->count(2)->create();

        $this->vehicles = Vehicle::query()
            ->orderByDesc('id')
            ->get();
    }

    public function test_get_all_vehicles(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehiclesQuery::NAME)
                ->args([
                    'per_page' => $this->vehicles->count()
                ])
                ->select([
                    'data' => [
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
                            'id'
                        ],
                        'make' => [
                            'id'
                        ],
                        'model' => [
                            'id'
                        ],
                        'client' => [
                            'id',
                            'manager' => [
                                'id'
                            ]
                        ],
                        'schema' => [
                            'id',
                            'image',
                        ],
                        'odo',
                        'active'
                    ]
                ])
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    BaseVehiclesQuery::NAME => [
                        'data' => [
                            '*' => [
                                'id',
                                'created_at',
                                'updated_at',
                                'vin',
                                'state_number',
                                'form',
                                'class' => [
                                    'id'
                                ],
                                'type' => [
                                    'id'
                                ],
                                'make' => [
                                    'id'
                                ],
                                'model' => [
                                    'id'
                                ],
                                'client' => [
                                    'id',
                                    'manager' => [
                                        'id'
                                    ]
                                ],
                                'schema' => [
                                    'id'
                                ],
                                'odo',
                                'active'
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    BaseVehiclesQuery::NAME => [
                        'data' => $this
                            ->vehicles
                            ->map(
                                fn (Vehicle $vehicle) => [
                                    'id' => $vehicle->id,
                                    'created_at' => $vehicle->created_at->getTimestamp(),
                                    'updated_at' => $vehicle->updated_at->getTimestamp(),
                                    'state_number' => $vehicle->state_number,
                                    'vin' => $vehicle->vin,
                                    'form' => $vehicle->form->value,
                                    'class' => [
                                        'id' => $vehicle->class_id,
                                    ],
                                    'type' => [
                                        'id' => $vehicle->type_id
                                    ],
                                    'make' => [
                                        'id' => $vehicle->make_id
                                    ],
                                    'model' => [
                                        'id' => $vehicle->model_id
                                    ],
                                    'client' => [
                                        'id' => $vehicle->client_id,
                                        'manager' => [
                                            'id' => $vehicle->client->manager_id
                                        ]
                                    ],
                                    'schema' => [
                                        'id' => $vehicle->schema_id
                                    ],
                                    'odo' => $vehicle->odo,
                                    'active' => $vehicle->active
                                ]
                            )
                            ->values()
                            ->toArray()
                    ]
                ]
            ])
            ->assertJsonCount($this->vehicles->count(), 'data.' . BaseVehiclesQuery::NAME . '.data');
    }

    public function test_filter_by_id(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehiclesQuery::NAME)
                ->args([
                    'id' => $this->vehicles[0]->id,
                ])
                ->select([
                    'data' => [
                        'id',
                    ]
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    BaseVehiclesQuery::NAME => [
                        'data' => [
                            [
                                'id' => $this->vehicles[0]->id
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.' . BaseVehiclesQuery::NAME . '.data');
    }

    public function test_filter_by_state_number(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehiclesQuery::NAME)
                ->args([
                    'state_number' => $this->vehicles[1]->state_number,
                ])
                ->select([
                    'data' => [
                        'id',
                    ]
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    BaseVehiclesQuery::NAME => [
                        'data' => [
                            [
                                'id' => $this->vehicles[1]->id
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.' . BaseVehiclesQuery::NAME . '.data');
    }

    public function test_filter_by_vin(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehiclesQuery::NAME)
                ->args([
                    'vin' => $this->vehicles[2]->vin,
                ])
                ->select([
                    'data' => [
                        'id',
                    ]
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    BaseVehiclesQuery::NAME => [
                        'data' => [
                            [
                                'id' => $this->vehicles[2]->id
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.' . BaseVehiclesQuery::NAME . '.data');
    }

    public function test_filter_by_form(): void
    {
        $result = $this
            ->vehicles
            ->filter(fn (Vehicle $vehicle) => $vehicle->form->value === VehicleFormEnum::TRAILER)
            ->values()
            ->map(
                fn (Vehicle $vehicle) => [
                    'id' => $vehicle->id
                ]
            )
            ->toArray();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehiclesQuery::NAME)
                ->args([
                    'per_page' => count($result),
                           'form' => VehicleFormEnum::TRAILER(),
                       ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseVehiclesQuery::NAME => [
                            'data' => $result
                        ]
                    ]
                ]
            )
            ->assertJsonCount(count($result), 'data.' . BaseVehiclesQuery::NAME . '.data');
    }

    public function test_filter_by_class_id(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehiclesQuery::NAME)
                ->args(
                    [
                        'per_page' => $this->vehicles->count(),
                        'class_id' => $this->vehicles[0]->class_id,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseVehiclesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->vehicles[0]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseVehiclesQuery::NAME . '.data');
    }

    public function test_get_vehicle_with_inspection(): void
    {
        $inspection = Inspection::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseVehiclesQuery::NAME)
                ->args(
                    [
                        'id' => $inspection->vehicle_id
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                            'inspections' => [
                                'id'
                            ],
                            'last_inspection' => [
                                'id'
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseVehiclesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $inspection->vehicle_id,
                                    'inspections' => [
                                        [
                                            'id' => $inspection->id,
                                        ]
                                    ],
                                    'last_inspection' => [
                                        'id' => $inspection->id
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }
}
