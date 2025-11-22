<?php


namespace Tests\Feature\Mutations\BackOffice\Catalog\Solutions;


use App\GraphQL\Mutations\BackOffice\Catalog\Solutions\SolutionDeleteMutation;
use App\Models\Catalog\Solutions\Solution;
use App\Permissions\Catalog\Solutions\SolutionDeletePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SolutionDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public function test_delete_solution_setting(): void
    {
        $solution = Solution::factory()
            ->outdoor()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SolutionDeleteMutation::NAME)
                ->args(
                    [
                        'product_id' => $solution->product_id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionDeleteMutation::NAME => true
                    ]
                ]
            );
    }

    public function test_delete_indoor_solution_setting_with_parent(): void
    {
        $solution = Solution::factory()
            ->children(
                Solution::factory()
                    ->count(1)
                    ->indoor()
                    ->create()
            )
            ->outdoor()
            ->create();

        $indoor = $solution->children()
            ->first();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SolutionDeleteMutation::NAME)
                ->args(
                    [
                        'product_id' => $indoor->product_id
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
                                'validation.custom.catalog.solutions.cant_change_type_and_delete',
                                ['product' => $solution->product->title]
                            )
                        ]
                    ]
                ]
            );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([SolutionDeletePermission::KEY]);
    }
}
