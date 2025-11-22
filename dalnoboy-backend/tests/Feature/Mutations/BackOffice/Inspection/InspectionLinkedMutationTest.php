<?php

namespace Tests\Feature\Mutations\BackOffice\Inspection;

use App\GraphQL\Mutations\BackOffice\Inspections\InspectionLinkedMutation;
use App\Models\Inspections\Inspection;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InspectionLinkedMutationTest extends TestCase
{
    use DatabaseTransactions;

    private User $inspector;

    public function setUp(): void
    {
        parent::setUp();

        $this->inspector = User::factory()->create();
        $this->loginAsAdminWithRole();
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

        $res = $this->postGraphQLBackOffice(
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

    public function test_linked_inspections_with_old_main_inspection(): void
    {
        $mainInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->create(['created_at' => now()->addHours(-73)]);
        $trailerInspection = Inspection::factory()
            ->forInspector($this->inspector)
            ->forTrailer()
            ->create();

        $this->postGraphQLBackOffice(
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
                    'data' => [
                        InspectionLinkedMutation::NAME => [
                            'id' => $mainInspection->id,
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

        $this->postGraphQLBackOffice(
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
                    'data' => [
                        InspectionLinkedMutation::NAME => [
                            'id' => $mainInspection->id,
                        ]
                    ]
                ]
            );
    }
}
