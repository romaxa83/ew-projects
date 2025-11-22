<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Problems;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Problems\ProblemToggleActiveMutation;
use App\Models\Dictionaries\Problem;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProblemToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_toggle_active_problem(): void
    {
        $problem = Problem::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ProblemToggleActiveMutation::NAME)
                ->args(
                    [
                        'ids' => [$problem->id],
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ProblemToggleActiveMutation::NAME => true,
                    ],
                ]
            );

        $problem->refresh();
        $this->assertFalse((bool) $problem->active);
    }
}
