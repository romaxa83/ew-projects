<?php


namespace Tests\Feature\Mutations\FrontOffice\Inspection;


use App\GraphQL\Mutations\FrontOffice\Inspections\InspectionLinkedMutation;
use App\Models\Inspections\Inspection;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class InspectionLinkedMutationTest extends TestCase
{
    use DatabaseTransactions;

    private User $inspector;

    public function setUp(): void
    {
        parent::setUp();

        $this->inspector = $this->loginAsUserWithRole();
    }

    public function test_linked_inspections(): void
    {
        $mainInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->create();
        $trailerInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->forTrailer()
            ->create();

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionLinkedMutation::NAME)
                ->args(
                    [
                        'linked_inspections' => [
                            'main_inspection_id' => $mainInspection->id,
                            'trailer_inspection_id' => $trailerInspection->id,
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'trailer_inspection' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        InspectionLinkedMutation::NAME => [
                            'id',
                            'trailer_inspection' => [
                                'id'
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionLinkedMutation::NAME => [
                            'id' => $mainInspection->id,
                            'trailer_inspection' => [
                                'id' => $trailerInspection->id
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_linked_inspections_with_similar_vehicle_form1(): void
    {
        $mainInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->create();
        $trailerInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->create();

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionLinkedMutation::NAME)
                ->args(
                    [
                        'linked_inspections' => [
                            'main_inspection_id' => $mainInspection->id,
                            'trailer_inspection_id' => $trailerInspection->id,
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'trailer_inspection' => [
                            'id'
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
                            'extensions' => [
                                'validation' => [
                                    'linked_inspections.trailer_inspection_id' => [
                                        trans('inspections.linked.incorrect_vehicle_form')
                                    ]
                                ]
                            ],
                            'errorCode' => Response::HTTP_UNPROCESSABLE_ENTITY
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_linked_inspections_with_similar_vehicle_form2(): void
    {
        $mainInspection = Inspection::factory()
            ->forTrailer()
            ->forInspector($this->inspector)
            ->create();
        $trailerInspection = Inspection::factory()
            ->forTrailer()
            ->forInspector($this->inspector)
            ->create();

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionLinkedMutation::NAME)
                ->args(
                    [
                        'linked_inspections' => [
                            'main_inspection_id' => $mainInspection->id,
                            'trailer_inspection_id' => $trailerInspection->id,
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'trailer_inspection' => [
                            'id'
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
                            'extensions' => [
                                'validation' => [
                                    'linked_inspections.main_inspection_id' => [
                                        trans('inspections.linked.incorrect_vehicle_form')
                                    ]
                                ]
                            ],
                            'errorCode' => Response::HTTP_UNPROCESSABLE_ENTITY
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_linked_inspections_to_vehicle_with_linked1(): void
    {
        $mainInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->create();
        $trailerInspection = Inspection::factory()
            ->forTrailer()
            ->for(
                Inspection::factory()
                    ->forInspector($this->inspector)
                    ->create(),
                'main'
            )
            ->forInspector($this->inspector)
            ->create();

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionLinkedMutation::NAME)
                ->args(
                    [
                        'linked_inspections' => [
                            'main_inspection_id' => $mainInspection->id,
                            'trailer_inspection_id' => $trailerInspection->id,
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'trailer_inspection' => [
                            'id'
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
                            'extensions' => [
                                'validation' => [
                                    'linked_inspections.trailer_inspection_id' => [
                                        trans('inspections.linked.trailer_has_main')
                                    ]
                                ]
                            ],
                            'errorCode' => Response::HTTP_UNPROCESSABLE_ENTITY
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_linked_inspections_to_vehicle_with_linked2(): void
    {
        $mainInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->has(
                Inspection::factory()
                    ->forTrailer()
                    ->forInspector($this->inspector),
                'trailer'
            )
            ->create();
        $trailerInspection = Inspection::factory()
            ->forTrailer()
            ->forInspector($this->inspector)
            ->create();

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionLinkedMutation::NAME)
                ->args(
                    [
                        'linked_inspections' => [
                            'main_inspection_id' => $mainInspection->id,
                            'trailer_inspection_id' => $trailerInspection->id,
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'trailer_inspection' => [
                            'id'
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
                            'extensions' => [
                                'validation' => [
                                    'linked_inspections.main_inspection_id' => [
                                        trans('inspections.linked.main_has_trailer')
                                    ]
                                ]
                            ],
                            'errorCode' => Response::HTTP_UNPROCESSABLE_ENTITY
                        ]
                    ]
                ]
            );
    }

    public function test_linked_inspections_with_old_main_inspection(): void
    {
        $mainInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->create(['created_at' => now()->addHours(-73)]);
        $trailerInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->forTrailer()
            ->create();

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionLinkedMutation::NAME)
                ->args(
                    [
                        'linked_inspections' => [
                            'main_inspection_id' => $mainInspection->id,
                            'trailer_inspection_id' => $trailerInspection->id,
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
                            'message' => trans('inspections.can_not_update')
                        ]
                    ]
                ]
            );
    }

    public function test_linked_inspections_with_old_trailer_inspection(): void
    {
        $mainInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->create();
        $trailerInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->forTrailer()
            ->create(['created_at' => now()->addHours(-73)]);

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionLinkedMutation::NAME)
                ->args(
                    [
                        'linked_inspections' => [
                            'main_inspection_id' => $mainInspection->id,
                            'trailer_inspection_id' => $trailerInspection->id,
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
                            'message' => trans('inspections.can_not_update')
                        ]
                    ]
                ]
            );
    }
}
