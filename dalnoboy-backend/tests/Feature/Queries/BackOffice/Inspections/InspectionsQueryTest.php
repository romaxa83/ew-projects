<?php


namespace Tests\Feature\Queries\BackOffice\Inspections;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Queries\Common\Inspections\BaseInspectionsQuery;
use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\Recommendation;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Models\Tires\Tire;
use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use Carbon\CarbonImmutable;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InspectionsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_get_inspections(): void
    {
        $inspection = Inspection::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'inspector' => [
                                'id'
                            ],
                            'vehicle' => [
                                'id'
                            ],
                            'driver' => [
                                'id'
                            ],
                            'is_moderated',
                            'unable_to_sign',
                            'inspection_reason' => [
                                'id'
                            ],
                            'inspection_reason_description',
                            'tires' => [
                                'id',
                                'tire' => [
                                    'id'
                                ],
                                'schema_wheel' => [
                                    'id'
                                ],
                                'ogp',
                                'pressure',
                                'comment',
                                'no_problems',
                                'problems' => [
                                    'id'
                                ],
                                'recommendations' => [
                                    'id'
                                ]
                            ],
                            'photos' => [
                                Inspection::MC_STATE_NUMBER => [
                                    'id'
                                ],
                                Inspection::MC_VEHICLE => [
                                    'id'
                                ],
                                Inspection::MC_DATA_SHEET_1 => [
                                    'id'
                                ],
                                Inspection::MC_DATA_SHEET_2 => [
                                    'id'
                                ],
                                Inspection::MC_ODO => [
                                    'id'
                                ],
                                Inspection::MC_SIGN => [
                                    'id'
                                ],
                            ],
                            'moderation_fields' => [
                                'field',
                                'message',
                            ],
                            'trailer_inspection' => [
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
                        BaseInspectionsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $inspection->id,
                                    'inspector' => [
                                        'id' => $inspection->inspector_id
                                    ],
                                    'vehicle' => [
                                        'id' => $inspection->vehicle_id
                                    ],
                                    'driver' => [
                                        'id' => $inspection->driver_id
                                    ],
                                    'is_moderated' => $inspection->is_moderated,
                                    'unable_to_sign' => $inspection->unable_to_sign,
                                    'inspection_reason' => [
                                        'id' => $inspection->inspection_reason_id
                                    ],
                                    'inspection_reason_description' => $inspection->inspection_reason_description,
                                    'tires' => $inspection
                                        ->inspectionTires
                                        ->map(
                                            fn(InspectionTire $inspectionTire) => [
                                                'id' => $inspectionTire->id,
                                                'tire' => [
                                                    'id' => $inspectionTire->tire_id
                                                ],
                                                'schema_wheel' => [
                                                    'id' => $inspectionTire->schema_wheel_id
                                                ],
                                                'ogp' => $inspectionTire->ogp,
                                                'pressure' => $inspectionTire->pressure,
                                                'comment' => $inspectionTire->comment,
                                                'no_problems' => $inspectionTire->no_problems,
                                                'problems' => $inspectionTire
                                                    ->problems
                                                    ->map(
                                                        fn(Problem $problem) => [
                                                            'id' => $problem->id
                                                        ]
                                                    )
                                                    ->values()
                                                    ->toArray(),
                                                'recommendations' => $inspectionTire
                                                    ->recommendations
                                                    ->map(
                                                        fn(Recommendation $recommendation) => [
                                                            'id' => $recommendation->id
                                                        ]
                                                    )
                                                    ->values()
                                                    ->toArray()
                                            ]
                                        )
                                        ->values()
                                        ->toArray(),
                                    'photos' => [
                                        Inspection::MC_STATE_NUMBER => [
                                            'id' => $inspection->getFirstMedia(Inspection::MC_STATE_NUMBER)->id
                                        ],
                                        Inspection::MC_VEHICLE => [
                                            'id' => $inspection->getFirstMedia(Inspection::MC_VEHICLE)->id
                                        ],
                                        Inspection::MC_DATA_SHEET_1 => [
                                            'id' => $inspection->getFirstMedia(Inspection::MC_DATA_SHEET_1)->id
                                        ],
                                        Inspection::MC_DATA_SHEET_2 => [
                                            'id' => $inspection->getFirstMedia(Inspection::MC_DATA_SHEET_2)->id
                                        ],
                                        Inspection::MC_ODO => [
                                            'id' => $inspection->getFirstMedia(Inspection::MC_ODO)->id
                                        ],
                                        Inspection::MC_SIGN => [
                                            'id' => $inspection->getFirstMedia(Inspection::MC_SIGN)->id
                                        ],
                                    ],
                                    'moderation_fields' => $inspection->moderation_fields,
                                    'trailer_inspection' => null
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_linked_inspections(): void
    {
        $main = Inspection::factory()
            ->linkTrailer(
                Inspection::factory()
                    ->forTrailer()
            )
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
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
                        BaseInspectionsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $main->id
                                ],
                                [
                                    'id' => $main->trailer->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_filter_by_linked_vehicle_form(): void
    {
        $main = Inspection::factory()
            ->linkTrailer(
                Inspection::factory()
                    ->forTrailer()
            )
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'vehicle_form' => VehicleFormEnum::TRAILER()
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
                        BaseInspectionsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $main->trailer->id,
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_filter_by_linked_state_number(): void
    {
        $main = Inspection::factory()
            ->linkTrailer(
                Inspection::factory()
                    ->forTrailer()
            )
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'state_number' => $main->trailer->vehicle->state_number
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
                        BaseInspectionsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $main->trailer->id,
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_filter_moderated(): void
    {
        $main = Inspection::factory()
            ->notModerated()
            ->create();

        Inspection::factory()
            ->count(4)
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'moderated' => false
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
                        BaseInspectionsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $main->id,
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_filter_by_state_number_with_connection(): void
    {
        $vehicle = Vehicle::factory()->create();
        $linked = Inspection::factory()
            ->linkTrailer(
                Inspection::factory()
                    ->forTrailer()
            )
            ->for($vehicle)
            ->create();

        Inspection::factory()
            ->for($vehicle)
            ->create();

        Inspection::factory()
            ->forTrailer()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'without_connection' => false,
                        'state_number' => $linked->vehicle->state_number
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                            'vehicle' => [
                                'state_number',
                            ],
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseInspectionsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $linked->id,
                                    'vehicle' => [
                                        'state_number' => $linked->vehicle->state_number,
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_filter_by_state_number_without_connection(): void
    {
        $vehicle = Vehicle::factory()->create();
        Inspection::factory()
            ->linkTrailer(
                Inspection::factory()
                    ->forTrailer()
            )
            ->for($vehicle)
            ->create();

        $withoutConnection = Inspection::factory()
            ->for($vehicle)
            ->create();

        Inspection::factory()
            ->forTrailer()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'without_connection' => true,
                        'state_number' => $withoutConnection->vehicle->state_number
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                            'vehicle' => [
                                'state_number',
                            ],
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseInspectionsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $withoutConnection->id,
                                    'vehicle' => [
                                        'state_number' => $withoutConnection->vehicle->state_number,
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_filter_by_inspector(): void
    {
        $inspector1 = User::factory()->inspector()->create();
        $inspector2 = User::factory()->inspector()->create();
        Inspection::factory()->forInspector($inspector1)->create();
        $inspection = Inspection::factory()->forInspector($inspector2)->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'inspector' => $inspector2->id,
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
                        BaseInspectionsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $inspection->id,
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_get_inspections_with_previous_tire_inspection(): void
    {
        $inspection = Inspection::factory()
            ->create();

        $previousInspection = Inspection::factory()
            ->create();

        $tire = Tire::factory()->create();
        $schema = $previousInspection->vehicle->schemaVehicle->wheels[0];
        InspectionTire::factory()
            ->for($tire)
            ->for($previousInspection)
            ->for($schema)
            ->create(['ogp' => 100]);

        InspectionTire::factory()
            ->for($tire)
            ->for($inspection)
            ->for($schema)
            ->create(['ogp' => 200]);

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'id' => $inspection->id,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'tires' => [
                                'previous_inspection_ogp',
                            ],
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseInspectionsQuery::NAME => [
                            'data' => [
                                [
                                    'tires' => $inspection
                                        ->inspectionTires
                                        ->map(
                                            fn(InspectionTire $inspectionTire) => [
                                                'previous_inspection_ogp' => $inspectionTire->previousTireInspection()?->ogp,
                                            ]
                                        )
                                        ->values()
                                        ->toArray(),
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }


}
