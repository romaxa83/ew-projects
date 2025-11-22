<?php

namespace Tests\Feature\Mutations\BackOffice\Inspection;

use App\Enums\Inspections\InspectionModerationEntityEnum;
use App\Enums\Inspections\InspectionModerationFieldEnum;
use App\GraphQL\Mutations\BackOffice\Inspections\InspectionTireUpdateMutation;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Models\Tires\Tire;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InspectionTireUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_inspection(): void
    {
        $inspection = Inspection::factory()
            ->create();

        $inspectionTire = $inspection->inspectionTires[0];
        $ogp = $inspectionTire->ogp - 0.2;

        $data = [
            'id' => $inspectionTire->id,
            'ogp' => $ogp,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionTireUpdateMutation::NAME)
                ->args($data)
                ->select(
                    [
                        'tires' => [
                            'id',
                            'ogp',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionTireUpdateMutation::NAME => [
                            'tires' => [
                                '*' => [
                                    'id',
                                    'ogp',
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(InspectionTire::class, $data);
    }

    public function test_update_inspection_with_ogp_more_than_ngp(): void
    {
        $inspection = Inspection::factory()
            ->create();

        $inspectionTire = $inspection->inspectionTires[0];
        $ogp = $inspectionTire->tire->specification->ngp + 0.2;

        $data = [
            'id' => $inspectionTire->id,
            'ogp' => $ogp,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionTireUpdateMutation::NAME)
                ->args($data)
                ->select(
                    [
                        'tires' => [
                            'id',
                            'ogp',
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
                            'message' => trans('inspections.validation_messages.tire.ogp_bigger_ngp')
                        ]
                    ]
                ]
            );
    }

    public function test_update_inspection_with_to_big_ogp(): void
    {
        $previousInspection = Inspection::factory()
            ->create();

        $tire = Tire::factory()->create();
        $schema = $previousInspection->vehicle->schemaVehicle->wheels[0];
        $tireInspectionPrevious = InspectionTire::factory()
            ->for($tire)
            ->for($previousInspection)
            ->for($schema)
            ->create(['ogp' => $tire->specification->ngp - 2]);

        $inspection = Inspection::factory()
            ->create();
        $tireInspectionNew = InspectionTire::factory()
            ->for($tire)
            ->for($inspection)
            ->for($schema)
            ->create();
        $inspection->moderation_fields = [
            [
                'id' => $tireInspectionNew->id,
                'field' => InspectionModerationFieldEnum::OGP,
                'entity' => InspectionModerationEntityEnum::TIRE,
                'message' => 'inspections.validation_messages.tire.ogp_too_big'
            ]
        ];
        $inspection->save();

        $ogp = $tireInspectionPrevious->ogp + 0.2;

        $data = [
            'id' => $tireInspectionNew->id,
            'ogp' => $ogp,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionTireUpdateMutation::NAME)
                ->args($data)
                ->select(
                    [
                        'tires' => [
                            'id',
                            'ogp',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionTireUpdateMutation::NAME => [
                            'tires' => [
                                '*' => [
                                    'id',
                                    'ogp',
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(InspectionTire::class, $data);

        $inspection->refresh();
        $this->assertEquals(
            [
                [
                    'id' => $tireInspectionNew->id,
                    'field' => InspectionModerationFieldEnum::OGP,
                    'entity' => InspectionModerationEntityEnum::TIRE,
                    'message' => 'inspections.validation_messages.tire.ogp_too_big'
                ]
            ],
            $inspection->moderation_fields
        );
    }

    public function test_update_inspection_with_correct_ogp(): void
    {
        $previousInspection = Inspection::factory()
            ->create();

        $tire = Tire::factory()->create();
        $schema = $previousInspection->vehicle->schemaVehicle->wheels[0];
        $tireInspectionPrevious = InspectionTire::factory()
            ->for($tire)
            ->for($previousInspection)
            ->for($schema)
            ->create(['ogp' => $tire->specification->ngp - 2]);

        $inspection = Inspection::factory()
            ->create();
        $tireInspectionNew = InspectionTire::factory()
            ->for($tire)
            ->for($inspection)
            ->for($schema)
            ->create();
        $inspection->moderation_fields = [
            [
                'id' => $tireInspectionNew->id,
                'field' => InspectionModerationFieldEnum::OGP,
                'entity' => InspectionModerationEntityEnum::TIRE,
                'message' => 'inspections.validation_messages.tire.ogp_too_big'
            ]
        ];
        $inspection->save();

        $ogp = 0;

        $data = [
            'id' => $tireInspectionNew->id,
            'ogp' => $ogp,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionTireUpdateMutation::NAME)
                ->args($data)
                ->select(
                    [
                        'moderation_fields' => [
                            'entity',
                            'id',
                            'field',
                            'message'
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        InspectionTireUpdateMutation::NAME => [
                            'moderation_fields' => null,
                        ]
                    ]
                ]
            );
    }

    public function test_update_inspection_with_to_big_ogp_and_other_fields(): void
    {
        $previousInspection = Inspection::factory()
            ->create();

        $tire = Tire::factory()->create();
        $schema = $previousInspection->vehicle->schemaVehicle->wheels[0];
        $tireInspectionPrevious = InspectionTire::factory()
            ->for($tire)
            ->for($previousInspection)
            ->for($schema)
            ->create(['ogp' => $tire->specification->ngp - 2]);

        $inspection = Inspection::factory()
            ->create();
        $tireInspectionNew = InspectionTire::factory()
            ->for($tire)
            ->for($inspection)
            ->for($schema)
            ->create();
        $inspection->moderation_fields = [
            [
                'id' => $tireInspectionNew->id,
                'field' => InspectionModerationFieldEnum::OGP,
                'entity' => InspectionModerationEntityEnum::TIRE,
                'message' => 'inspections.validation_messages.tire.ogp_too_big'
            ],
            [
                'id' => $inspection->inspectionTires->first()->id,
                'field' => InspectionModerationFieldEnum::OGP,
                'entity' => InspectionModerationEntityEnum::TIRE,
                'message' => 'inspections.validation_messages.tire.ogp_too_big'
            ]
        ];
        $inspection->save();

        $ogp = $tireInspectionPrevious->ogp + 0.2;

        $data = [
            'id' => $tireInspectionNew->id,
            'ogp' => $ogp,
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(InspectionTireUpdateMutation::NAME)
                ->args($data)
                ->select(
                    [
                        'tires' => [
                            'id',
                            'ogp',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionTireUpdateMutation::NAME => [
                            'tires' => [
                                '*' => [
                                    'id',
                                    'ogp',
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(InspectionTire::class, $data);

        $inspection->refresh();
        $this->assertEquals(
            [
                [
                    'id' => $inspection->inspectionTires->first()->id,
                    'field' => InspectionModerationFieldEnum::OGP,
                    'entity' => InspectionModerationEntityEnum::TIRE,
                    'message' => 'inspections.validation_messages.tire.ogp_too_big'
                ],
                [
                    'id' => $tireInspectionNew->id,
                    'field' => InspectionModerationFieldEnum::OGP,
                    'entity' => InspectionModerationEntityEnum::TIRE,
                    'message' => 'inspections.validation_messages.tire.ogp_too_big'
                ]
            ],
            $inspection->moderation_fields
        );
    }
}
