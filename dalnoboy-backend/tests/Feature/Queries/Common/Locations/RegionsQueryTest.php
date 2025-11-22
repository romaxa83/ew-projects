<?php


namespace Tests\Feature\Queries\Common\Locations;


use App\GraphQL\Queries\Common\Locations\BaseRegionsQuery;
use App\Models\Locations\Region;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegionsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_regions_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        /**
         * @see TestCase::postGraphQLBackOffice()
         * @see TestCase::postGraphQL()
         */
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseRegionsQuery::NAME)
                ->select(
                    [
                        'id',
                        'slug',
                        'translate' => [
                            'title',
                            'language'
                        ],
                        'translates' => [
                            'title',
                            'language'
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseRegionsQuery::NAME => [
                            '*' => [
                                'id',
                                'slug',
                                'translate' => [
                                    'title',
                                    'language'
                                ],
                                'translates' => [
                                    '*' => [
                                        'title',
                                        'language'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                Region::query()
                    ->count(),
                'data.' . BaseRegionsQuery::NAME
            );
    }

    public function test_get_regions_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }
}
