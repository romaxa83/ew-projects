<?php


namespace Tests\Feature\Queries\BackOffice\Catalog\Solutions;


use App\GraphQL\Queries\BackOffice\Catalog\Solutions\SolutionSeriesListQuery;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Permissions\Catalog\Solutions\SolutionReadPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Seeders\Catalog\Solutions\SolutionSettingSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SolutionSeriesListQueryTest extends TestCase
{
    use AdminManagerHelperTrait;
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([SolutionReadPermission::KEY]);

        $this->seed(SolutionSettingSeeder::class);
    }

    public function test_get_series_list(): void
    {
        $count = SolutionSeries::query()
            ->count();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SolutionSeriesListQuery::NAME)
                ->select(
                    [
                        'id',
                        'translation' => [
                            'id',
                            'title',
                            'language'
                        ],
                        'translations' => [
                            'id',
                            'title',
                            'language'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        SolutionSeriesListQuery::NAME => [
                            '*' => [
                                'id',
                                'translation' => [
                                    'id',
                                    'title',
                                    'language'
                                ],
                                'translations' => [
                                    '*' => [
                                        'id',
                                        'title',
                                        'language'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount($count, 'data.' . SolutionSeriesListQuery::NAME);
    }
}
