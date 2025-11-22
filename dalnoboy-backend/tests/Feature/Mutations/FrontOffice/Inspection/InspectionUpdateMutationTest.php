<?php


namespace Tests\Feature\Mutations\FrontOffice\Inspection;


use App\GraphQL\Mutations\FrontOffice\Inspections\InspectionUpdateMutation;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Models\Users\User;
use Carbon\Carbon;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InspectionUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private User $inspector;

    public function setUp(): void
    {
        parent::setUp();

        $this->inspector = $this->loginAsUserWithRole();
    }

    public function test_try_to_update_after_72_hours(): void
    {
        $inspection = Inspection::factory(['inspector_id' => $this->inspector->id])
            ->create();

        $data = [
            'vehicle_id' => $inspection->vehicle_id,
            'driver_id' => $inspection->driver_id,
            'inspection_reason_id' => $inspection->inspection_reason_id,
            'unable_to_sign' => $inspection->unable_to_sign,
            'odo' => $inspection->odo,
            'time' => Carbon::now()
                    ->getTimestamp() + 259400,
            'tires' => $inspection
                ->inspectionTires
                ->map(
                    fn(InspectionTire $tire) => [
                        'tire_id' => $tire->tire_id,
                        'schema_wheel_id' => $tire->schema_wheel_id,
                        'ogp' => 6,
                        'pressure' => 2.2,

                    ]
                )
                ->toArray()
        ];

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $inspection->id,
                        'inspection' => $data
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
                            'message' => trans('inspections.can_not_update')
                        ]
                    ]
                ]
            );
    }

    public function test_update_inspection(): void
    {
        $inspection = Inspection::factory(['inspector_id' => $this->inspector->id])
            ->create();

        $data = [
            'vehicle_id' => $inspection->vehicle_id,
            'driver_id' => $inspection->driver_id,
            'inspection_reason_id' => $inspection->inspection_reason_id,
            'unable_to_sign' => $inspection->unable_to_sign,
            'odo' => $inspection->odo,
            'time' => Carbon::now()
                ->getTimestamp(),
            'tires' => $inspection
                ->inspectionTires
                ->map(
                    fn(InspectionTire $tire) => [
                        'tire_id' => $tire->tire_id,
                        'schema_wheel_id' => $tire->schema_wheel_id,
                        'ogp' => 6,
                        'pressure' => 2.2,
                        'comment' => 'test comment',
                    ]
                )
                ->toArray()
        ];

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $inspection->id,
                        'inspection' => $data
                    ]
                )
                ->select(
                    [
                        'id',
                        'inspector' => [
                            'id',
                        ],
                        'vehicle' => [
                            'id',
                        ],
                        'is_moderated',
                        'unable_to_sign',
                        'driver' => [
                            'id'
                        ],
                        'inspection_reason' => [
                            'id',
                        ],
                        'inspection_reason_description',
                        'photos' => [
                            Inspection::MC_STATE_NUMBER => [
                                'id',
                            ],
                            Inspection::MC_VEHICLE => [
                                'id',
                            ],
                            Inspection::MC_DATA_SHEET_1 => [
                                'id',
                            ],
                            Inspection::MC_DATA_SHEET_2 => [
                                'id',
                            ],
                            Inspection::MC_ODO => [
                                'id',
                            ],
                            Inspection::MC_SIGN => [
                                'id',
                            ],
                        ],
                        'tires' => [
                            'tire' => [
                                'id',
                            ],
                            'schema_wheel' => [
                                'id',
                            ],
                            'ogp',
                            'pressure',
                            'comment',
                            'no_problems',
                            'problems' => [
                                'id',
                            ],
                            'recommendations' => [
                                'id',
                                'is_confirmed',
                                'new_tire' => [
                                    'id'
                                ]
                            ]
                        ],
                        'moderation_fields' => [
                            'field',
                            'message',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionUpdateMutation::NAME => [
                            'id',
                            'inspector' => [
                                'id',
                            ],
                            'vehicle' => [
                                'id',
                            ],
                            'is_moderated',
                            'inspection_reason' => [
                                'id',
                            ],
                            'inspection_reason_description',
                            'photos' => [
                                Inspection::MC_STATE_NUMBER => [
                                    'id',
                                ],
                                Inspection::MC_VEHICLE => [
                                    'id',
                                ],
                                Inspection::MC_DATA_SHEET_1 => [
                                    'id',
                                ],
                                Inspection::MC_DATA_SHEET_2 => [
                                    'id',
                                ],
                                Inspection::MC_ODO => [
                                    'id',
                                ],
                                Inspection::MC_SIGN => [
                                    'id',
                                ],
                            ],
                            'tires' => [
                                '*' => [
                                    'tire' => [
                                        'id',
                                    ],
                                    'schema_wheel' => [
                                        'id',
                                    ],
                                    'ogp',
                                    'pressure',
                                    'comment',
                                    'no_problems',
                                    'problems',
                                    'recommendations'
                                ]
                            ],
                            'moderation_fields'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionUpdateMutation::NAME => [
                            'id' => $inspection->id,
                            'inspector' => [
                                'id' => $inspection->inspector_id,
                            ],
                            'vehicle' => [
                                'id' => $inspection->vehicle_id,
                            ],
                            'is_moderated' => true,
                            'inspection_reason' => [
                                'id' => $inspection->inspection_reason_id,
                            ],
                            'inspection_reason_description' => null,
                            'photos' => [
                                Inspection::MC_STATE_NUMBER => [
                                    'id' => $inspection->getFirstMedia(Inspection::MC_STATE_NUMBER)->id,
                                ],
                                Inspection::MC_VEHICLE => [
                                    'id' => $inspection->getFirstMedia(Inspection::MC_VEHICLE)->id,
                                ],
                                Inspection::MC_DATA_SHEET_1 => [
                                    'id' => $inspection->getFirstMedia(Inspection::MC_DATA_SHEET_1)->id,
                                ],
                                Inspection::MC_DATA_SHEET_2 => [
                                    'id' => $inspection->getFirstMedia(Inspection::MC_DATA_SHEET_2)->id,
                                ],
                                Inspection::MC_ODO => [
                                    'id' => $inspection->getFirstMedia(Inspection::MC_ODO)->id,
                                ],
                                Inspection::MC_SIGN => [
                                    'id' => $inspection->getFirstMedia(Inspection::MC_SIGN)->id,
                                ],
                            ],
                            'tires' => $inspection
                                ->inspectionTires
                                ->map(
                                    fn(InspectionTire $tire) => [
                                        'tire' => [
                                            'id' => $tire->tire_id,
                                        ],
                                        'schema_wheel' => [
                                            'id' => $tire->schema_wheel_id,
                                        ],
                                        'ogp' => 6,
                                        'pressure' => 2.2,
                                        'comment' => 'test comment',
                                        'no_problems' => true,
                                        'problems' => [],
                                        'recommendations' => []
                                    ]
                                )
                                ->toArray(),
                            'moderation_fields' => []
                        ]
                    ]
                ]
            );
    }

    public function test_update_inspection_by_another_inspector(): void
    {
        $inspector = User::factory()->create();
        $inspection = Inspection::factory(['inspector_id' => $inspector->id])
            ->create();

        $data = [
            'vehicle_id' => $inspection->vehicle_id,
            'driver_id' => $inspection->driver_id,
            'inspection_reason_id' => $inspection->inspection_reason_id,
            'unable_to_sign' => $inspection->unable_to_sign,
            'odo' => $inspection->odo,
            'time' => Carbon::now()
                ->getTimestamp(),
            'tires' => $inspection
                ->inspectionTires
                ->map(
                    fn(InspectionTire $tire) => [
                        'tire_id' => $tire->tire_id,
                        'schema_wheel_id' => $tire->schema_wheel_id,
                        'ogp' => 6,
                        'pressure' => 2.2,

                    ]
                )
                ->toArray()
        ];

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $inspection->id,
                        'inspection' => $data
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
                            'message' => 'validation',
                        ]
                    ]
                ]
            );
    }

    public function test_update_inspection_by_admin(): void
    {
        $this->loginAsAdminWithRole();
        $inspector = User::factory()->create();
        $inspection = Inspection::factory(['inspector_id' => $inspector->id])
            ->create();

        $data = [
            'vehicle_id' => $inspection->vehicle_id,
            'driver_id' => $inspection->driver_id,
            'inspection_reason_id' => $inspection->inspection_reason_id,
            'unable_to_sign' => $inspection->unable_to_sign,
            'odo' => $inspection->odo,
            'time' => Carbon::now()
                ->getTimestamp(),
            'tires' => $inspection
                ->inspectionTires
                ->map(
                    fn(InspectionTire $tire) => [
                        'tire_id' => $tire->tire_id,
                        'schema_wheel_id' => $tire->schema_wheel_id,
                        'ogp' => 6,
                        'pressure' => 2.2,

                    ]
                )
                ->toArray()
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $inspection->id,
                        'inspection' => $data
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
                    'data' => [
                        InspectionUpdateMutation::NAME => [
                            'id' => $inspection->id,
                        ]
                    ]
                ]
            );
    }

    public function test_update_inspection_created_at_not_changed(): void
    {
        $creationTime = Carbon::now()->addHours(-1);
        $inspection = Inspection::factory(['inspector_id' => $this->inspector->id])
            ->create(['created_at' => $creationTime]);

        $data = [
            'vehicle_id' => $inspection->vehicle_id,
            'driver_id' => $inspection->driver_id,
            'inspection_reason_id' => $inspection->inspection_reason_id,
            'unable_to_sign' => $inspection->unable_to_sign,
            'odo' => $inspection->odo,
            'time' => Carbon::now()
                ->getTimestamp(),
            'tires' => $inspection
                ->inspectionTires
                ->map(
                    fn(InspectionTire $tire) => [
                        'tire_id' => $tire->tire_id,
                        'schema_wheel_id' => $tire->schema_wheel_id,
                        'ogp' => 6,
                        'pressure' => 2.2,

                    ]
                )
                ->toArray()
        ];

        $this->postGraphQL(
            GraphQLQuery::mutation(InspectionUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $inspection->id,
                        'inspection' => $data
                    ]
                )
                ->select(
                    [
                        'id',
                        'created_at',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        InspectionUpdateMutation::NAME => [
                            'id' => $inspection->id,
                            'created_at' => $creationTime->getTimestamp(),
                        ]
                    ]
                ]
            );
    }
}
