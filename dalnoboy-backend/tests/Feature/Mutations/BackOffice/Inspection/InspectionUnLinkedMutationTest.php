<?php

namespace Tests\Feature\Mutations\BackOffice\Inspection;

use App\GraphQL\Mutations\BackOffice\Inspections\InspectionUnLinkedMutation;
use App\Models\Inspections\Inspection;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InspectionUnLinkedMutationTest extends TestCase
{
    use DatabaseTransactions;

    private User $inspector;

    public function setUp(): void
    {
        parent::setUp();

        $this->inspector = User::factory()->create();
        $this->loginAsAdminWithRole();
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

        $this->postGraphQLBackOffice(
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

        $trailerInspection = $mainInspection->trailer;

        $this->postGraphQLBackOffice(
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
                    'data' => [
                        InspectionUnlinkedMutation::NAME => [
                            [
                                'id' => $mainInspection->id,
                            ],
                            [
                                'id' => $trailerInspection->id,
                            ],
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

        $trailerInspection = $mainInspection->trailer;

        $this->postGraphQLBackOffice(
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
                    'data' => [
                        InspectionUnlinkedMutation::NAME => [
                            [
                                'id' => $mainInspection->id,
                            ],
                            [
                                'id' => $trailerInspection->id,
                            ],
                        ]
                    ]
                ]
            );
    }
}
