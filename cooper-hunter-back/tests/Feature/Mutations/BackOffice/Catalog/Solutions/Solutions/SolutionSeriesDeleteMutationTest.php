<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Solutions\Solutions;

use App\GraphQL\Mutations\BackOffice\Catalog\Solutions\Series\SolutionSeriesDeleteMutation;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SolutionSeriesDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SolutionSeriesDeleteMutation::NAME;

    public function test_delete(): void
    {
        $this->loginAsSuperAdmin();

        $series = SolutionSeries::factory()->create();

        $this->assertModelExists($series);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(
                    [
                        'id' => $series->id,
                    ]
                )
                ->make()
        );

        $this->assertModelMissing($series);
    }
}