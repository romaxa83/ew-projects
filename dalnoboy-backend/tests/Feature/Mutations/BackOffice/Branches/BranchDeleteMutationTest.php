<?php


namespace Tests\Feature\Mutations\BackOffice\Branches;


use App\GraphQL\Mutations\BackOffice\Branches\BranchDeleteMutation;
use App\Models\Branches\Branch;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_delete_branch(): void
    {
        $this->loginAsAdminWithRole();
        $branch = Branch::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(BranchDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $branch->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BranchDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Branch::class,
            [
                'id' => $branch->id
            ]
        );
    }

    public function test_try_to_delete_branch_with_employees(): void
    {
        $this->loginAsAdminWithRole();

        $branch = Branch::factory()
            ->withEmployees()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(BranchDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $branch->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.branches.branch_has_employees_yet')
                        ]
                    ]
                ]
            );
    }
}
