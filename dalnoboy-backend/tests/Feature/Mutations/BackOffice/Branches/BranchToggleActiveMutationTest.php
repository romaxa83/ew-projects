<?php


namespace Tests\Feature\Mutations\BackOffice\Branches;


use App\GraphQL\Mutations\BackOffice\Branches\BranchToggleActiveMutation;
use App\Models\Branches\Branch;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_deactivate_branch(): void
    {
        $this->loginAsAdminWithRole();

        $branch = Branch::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(BranchToggleActiveMutation::NAME)
                ->args(
                    [
                        'id' => $branch->id,
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchToggleActiveMutation::NAME => [
                            'id' => $branch->id,
                            'active' => false
                        ]
                    ]
                ]
            );
    }

    public function test_try_deactivate_branch_with_users(): void
    {
        $this->loginAsAdminWithRole();

        $branch = Branch::factory()
            ->withEmployees()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(BranchToggleActiveMutation::NAME)
                ->args(
                    [
                        'id' => $branch->id,
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
                            'message' => trans(
                                'validation.custom.branches.branch_has_employees_yet',
                            )
                        ]
                    ]
                ]
            );
    }
}
