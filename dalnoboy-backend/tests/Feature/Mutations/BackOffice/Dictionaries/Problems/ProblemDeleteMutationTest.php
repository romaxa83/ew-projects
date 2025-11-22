<?php

namespace Tests\Feature\Mutations\BackOffice\Dictionaries\Problems;

use App\GraphQL\Mutations\BackOffice\Dictionaries\Problems\ProblemDeleteMutation;
use App\Models\Dictionaries\Problem;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProblemDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_problem(): void
    {
        $problem = Problem::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ProblemDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $problem->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ProblemDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Problem::class,
            [
                'id' => $problem->id,
            ]
        );
    }
}
