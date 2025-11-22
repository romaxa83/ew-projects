<?php

namespace Tests\Feature\Queries\BackOffice\Statistics;

use App\GraphQL\Queries\BackOffice\Statistics\SolutionStatisticsDownloadQuery;
use App\Models\Statistics\FindSolutionStatistic;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SolutionStatisticsDownloadQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = SolutionStatisticsDownloadQuery::NAME;

    public function test_get_statistics_download_link(): void
    {
        $this->loginAsSuperAdmin();

        foreach (range(1, 5) as $count) {
            FindSolutionStatistic::factory()
                ->indoorsCount($count)
                ->create();
        }

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(self::QUERY)
                ->args(
                    [
                        'date_from' => now()->subDay()->format('Y-m-d'),
                        'date_to' => now()->addDay()->format('Y-m-d'),
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY
                    ]
                ]
            );
    }
}