<?php


namespace Tests\Feature\Mutations\FrontOffice\Inspection;


use App\GraphQL\Mutations\FrontOffice\Inspections\InspectionUnLinkedMutation;
use App\Models\Inspections\Inspection;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class InspectionUnLinkedMutationTest extends TestCase
{
    use DatabaseTransactions;

    private User $inspector;

    public function setUp(): void
    {
        parent::setUp();

        $this->inspector = $this->loginAsUserWithRole();
    }

    public function test_unlinked_inspections(): void
    {
        $mainInspection = Inspection::factory()
            ->has(
                Inspection::factory()
                    ->forInspector($this->inspector)
                    ->forTrailer(),
                'trailer'
            )
            ->forInspector($this->inspector)
            ->create();

        $trailerInspection = $mainInspection->trailer;

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionUnLinkedMutation::NAME)
                ->args(
                    [
                        'unlinked_inspections' => [
                            'inspection_id' => $mainInspection->id,
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
                        InspectionUnLinkedMutation::NAME => [
                            '*' => [
                                'id',
                                'trailer_inspection'
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionUnLinkedMutation::NAME => [
                            [
                                'id' => $mainInspection->id,
                                'trailer_inspection' => null
                            ],
                            [
                                'id' => $trailerInspection->id,
                                'trailer_inspection' => null
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_try_unlinked_inspection_without_linked(): void
    {
        $mainInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->create();

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionUnLinkedMutation::NAME)
                ->args(
                    [
                        'unlinked_inspections' => [
                            'inspection_id' => $mainInspection->id,
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
                            'extensions' => [
                                'validation' => [
                                    'unlinked_inspections.inspection_id' => [
                                        trans('inspections.linked.main_has_not_trailer')
                                    ]
                                ]
                            ],
                            'errorCode' => Response::HTTP_UNPROCESSABLE_ENTITY
                        ]
                    ]
                ]
            );
    }

    public function test_unlinked_inspections_with_old_main(): void
    {
        $mainInspection = Inspection::factory()
            ->has(
                Inspection::factory()
                    ->forInspector($this->inspector)
                    ->forTrailer(),
                'trailer'
            )
            ->forInspector($this->inspector)
            ->create(['created_at' => now()->addHours(-73)]);

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionUnLinkedMutation::NAME)
                ->args(
                    [
                        'unlinked_inspections' => [
                            'inspection_id' => $mainInspection->id,
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

    public function test_unlinked_inspections_with_old_trailer(): void
    {
        $mainInspection = Inspection::factory()
            ->has(
                Inspection::factory(['created_at' => now()->addHours(-73)])
                    ->forInspector($this->inspector)
                    ->forTrailer(),
                'trailer'
            )
            ->forInspector($this->inspector)
            ->create();

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionUnLinkedMutation::NAME)
                ->args(
                    [
                        'unlinked_inspections' => [
                            'inspection_id' => $mainInspection->id,
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

    public function test_unlinked_inspections_by_trailer(): void
    {
        $mainInspection = Inspection::factory()
            ->has(
                Inspection::factory()
                    ->forInspector($this->inspector)
                    ->forTrailer(),
                'trailer'
            )
            ->forInspector($this->inspector)
            ->create();

        $trailerInspection = $mainInspection->trailer;

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionUnLinkedMutation::NAME)
                ->args(
                    [
                        'unlinked_inspections' => [
                            'inspection_id' => $trailerInspection->id,
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
                        InspectionUnLinkedMutation::NAME => [
                            '*' => [
                                'id',
                                'trailer_inspection'
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        InspectionUnLinkedMutation::NAME => [
                            [
                                'id' => $mainInspection->id,
                                'trailer_inspection' => null
                            ],
                            [
                                'id' => $trailerInspection->id,
                                'trailer_inspection' => null
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_unlinked_inspections_by_trailer_without_main(): void
    {
        $trailerInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->forTrailer()
            ->create();

        $this->postGraphQl(
            GraphQLQuery::mutation(InspectionUnLinkedMutation::NAME)
                ->args(
                    [
                        'unlinked_inspections' => [
                            'inspection_id' => $trailerInspection->id,
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
                                    'unlinked_inspections.inspection_id' => [
                                        trans('inspections.linked.main_has_not_trailer')
                                    ]
                                ]
                            ],
                            'errorCode' => Response::HTTP_UNPROCESSABLE_ENTITY
                        ]
                    ]
                ]
            );
    }
}
