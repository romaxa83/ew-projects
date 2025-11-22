<?php


namespace Tests\Feature\Queries\FrontOffice\Inspections;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Queries\Common\Inspections\BaseInspectionsQuery;
use App\Models\Dictionaries\Problem;
use App\Models\Dictionaries\Recommendation;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use Carbon\CarbonImmutable;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InspectionsQueryTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->loginAsUserWithRole();
    }

    public function test_get_inspections(): void
    {
        $inspection = Inspection::factory()
            ->forInspector($this->user)
            ->create();

        $this->postGraphQL(
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
                                'tire' => [
                                    'id',
                                    'specification' => [
                                        'size' => [
                                            'tire_width' => [
                                                'value'
                                            ]
                                        ]
                                    ],
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

    public function test_get_not_linked_inspections(): void
    {
        $date = CarbonImmutable::now();
        $main = Inspection::factory()
            ->forInspector($this->user)
            ->create(['created_at' => $date->subSeconds(3)]);

        $trailer = Inspection::factory()
            ->forInspector($this->user)
            ->forTrailer()
            ->create(['created_at' => $date->subSeconds(2)]);

        $this->postGraphQL(
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
                                    'id' => $trailer->id,
                                ],
                                [
                                    'id' => $main->id
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_linked_inspections(): void
    {
        $main = Inspection::factory()
            ->forInspector($this->user)
            ->linkTrailer(
                Inspection::factory()
                    ->forInspector($this->user)
                    ->forTrailer()
            )
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'trailer_inspection' => [
                                'id'
                            ],
                            'main_inspection' => [
                                'id',
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
                                    'id' => $main->id,
                                    'trailer_inspection' => [
                                        'id' => $main->trailer->id
                                    ],
                                    'main_inspection' => [],
                                ],
                                [
                                    'id' => $main->trailer->id,
                                    'trailer_inspection' => [],
                                    'main_inspection' => [
                                        'id' => $main->id
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_get_only_my_inspection(): void
    {
        $main = Inspection::factory()
            ->forInspector($this->user)
            ->create();

        Inspection::factory()
            ->count(3)
            ->create();

        $this->postGraphQL(
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
                                    'id' => $main->id,
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_filter_by_vehicle_form(): void
    {
        $main = Inspection::factory()
            ->forInspector($this->user)
            ->create();

        Inspection::factory()
            ->forInspector($this->user)
            ->forTrailer()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'vehicle_form' => VehicleFormEnum::MAIN()
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

    public function test_filter_not_linked(): void
    {
        Inspection::factory()
            ->forInspector($this->user)
            ->linkTrailer(
                Inspection::factory()
                    ->forInspector($this->user)
                    ->forTrailer()
            )
            ->create();

        $trailer = Inspection::factory()
            ->forInspector($this->user)
            ->forTrailer()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'without_connection' => true
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
                                    'id' => $trailer->id,
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_filter_linked(): void
    {
        $main = Inspection::factory()
            ->forInspector($this->user)
            ->linkTrailer(
                Inspection::factory()
                    ->forInspector($this->user)
                    ->forTrailer()
            )
            ->create();

        Inspection::factory()
            ->forInspector($this->user)
            ->forTrailer()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'without_connection' => false
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
                                ],
                                [
                                    'id' => $main->trailer->id,
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseInspectionsQuery::NAME . '.data');
    }

    public function test_filter_by_state_number(): void
    {
        $main = Inspection::factory()
            ->forInspector($this->user)
            ->create();

        Inspection::factory()
            ->forInspector($this->user)
            ->count(5)
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(
                    [
                        'state_number' => $main->vehicle->state_number
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

    public function test_filter_by_linked_state_number(): void
    {
        $main = Inspection::factory()
            ->forInspector($this->user)
            ->linkTrailer(
                Inspection::factory()
                    ->forInspector($this->user)
                    ->forTrailer()
            )
            ->create();

        $this->postGraphQL(
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

    public function test_get_previous_inspection(): void
    {
        $vehicle = Vehicle::factory()->create();
        $vehicle2 = Vehicle::factory()->create();

        $date = CarbonImmutable::now();

        $inspection1 = Inspection::factory()
            ->forInspector($this->user)
            ->create([
                'vehicle_id' => $vehicle->id,
                'created_at' => $date->subSeconds(20),
            ]);

        $inspection2 = Inspection::factory()
            ->forInspector($this->user)
            ->create(['vehicle_id' => $vehicle->id, 'created_at' => $date->subSeconds(19),]);

        $inspection3 = Inspection::factory()
            ->forInspector($this->user)
            ->create(['created_at' => $date->subSeconds(18)]);

        $inspection4 = Inspection::factory()
            ->forInspector($this->user)
            ->create(['vehicle_id' => $vehicle2->id, 'created_at' => $date->subSeconds(17),]);

        $inspection5 = Inspection::factory()
            ->forInspector($this->user)
            ->create(['vehicle_id' => $vehicle->id, 'created_at' => $date->subSeconds(16),]);

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'previous_inspection' => [
                                'id',
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
                                    'id' => $inspection5->id,
                                    'previous_inspection' => [
                                        'id' => $inspection2->id
                                    ],
                                ],
                                [
                                    'id' => $inspection4->id,
                                    'previous_inspection' => [],
                                ],
                                [
                                    'id' => $inspection3->id,
                                    'previous_inspection' => [],
                                ],
                                [
                                    'id' => $inspection2->id,
                                    'previous_inspection' => [
                                        'id' => $inspection1->id
                                    ],
                                ],
                                [
                                    'id' => $inspection1->id,
                                    'previous_inspection' => [],
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_inspection_is_mine(): void
    {
        $date = CarbonImmutable::now();

        $inspection1 = Inspection::factory()
            ->forInspector($this->user)
            ->create(['created_at' => $date->subSeconds(5)]);

        $inspection2 = Inspection::factory()
            ->forInspector(User::factory()->create())
            ->create(['created_at' => $date->subSeconds(4)]);

        $inspection3 = Inspection::factory()
            ->forInspector(User::factory()->create())
            ->create(['is_moderated' => false, 'created_at' => $date->subSeconds(3)]);

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args([
                    'only_mine' => false,
                ])
                ->select(
                    [
                        'data' => [
                            'id',
                            'is_mine',
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
                                    'id' => $inspection3->id,
                                    'is_mine' => false,
                                ],
                                [
                                    'id' => $inspection2->id,
                                    'is_mine' => false,
                                ],
                                [
                                    'id' => $inspection1->id,
                                    'is_mine' => true,
                                ],
                            ]
                        ]
                    ]
                ]
            );

    }

    public function test_get_inspection_region(): void
    {
        Inspection::factory()
            ->forInspector($this->user)
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'branch' => [
                                'region' => [
                                    'translate' => [
                                        'title'
                                    ],
                                ],
                            ],
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    BaseInspectionsQuery::NAME => [
                        'data' => [
                            '*' => [
                                'id',
                                'branch' => [
                                    'region' => [
                                        'translate' => [
                                            'title'
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ]
                ]
            ]);
    }

    public function test_has_relation_field(): void
    {
        $inspection1 = Inspection::factory()
            ->forInspector($this->user)
            ->linkTrailer(
                Inspection::factory()
                    ->forInspector($this->user)
                    ->forTrailer()
            )
            ->create();

        $inspection2 = Inspection::factory()
            ->forInspector($this->user)
            ->forTrailer()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(['id' => $inspection1->id])
                ->select(
                    [
                        'data' => [
                            'id',
                            'has_relation',
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
                                    'id' => $inspection1->id,
                                    'has_relation' => true,
                                ],
                            ]
                        ]
                    ]
                ]
            );

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(['id' => $inspection1->trailer->id])
                ->select(
                    [
                        'data' => [
                            'id',
                            'has_relation',
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
                                    'id' => $inspection1->trailer->id,
                                    'has_relation' => true,
                                ],
                            ]
                        ]
                    ]
                ]
            );

        $this->postGraphQL(
            GraphQLQuery::query(BaseInspectionsQuery::NAME)
                ->args(['id' => $inspection2->id])
                ->select(
                    [
                        'data' => [
                            'id',
                            'has_relation',
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
                                    'id' => $inspection2->id,
                                    'has_relation' => false,
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }
}
