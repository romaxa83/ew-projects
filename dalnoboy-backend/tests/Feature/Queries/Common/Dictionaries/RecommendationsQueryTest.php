<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseRecommendationsQuery;
use App\Models\Dictionaries\Recommendation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RecommendationsQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $recommendations;

    public function setUp(): void
    {
        parent::setUp();

        $this->recommendations = Recommendation::factory()
            ->count(15)
            ->create();
    }

    public function test_get_recommendations_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseRecommendationsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'problems' => [
                                'id',
                            ],
                            'regulations' => [
                                'id',
                            ],
                            'translate' => [
                                'title',
                                'language'
                            ],
                            'translates' => [
                                'title',
                                'language'
                            ],
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseRecommendationsQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'problems' => [
                                        '*' => [
                                            'id',
                                        ]
                                    ],
                                    'regulations' => [
                                        '*' => [
                                            'id',
                                        ]
                                    ],
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
                ]
            )
            ->assertJsonCount(
                Recommendation::query()
                    ->count(),
                'data.' . BaseRecommendationsQuery::NAME . '.data'
            );
    }

    public function test_get_recommendations_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->recommendations[0]->active = false;
        $this->recommendations[0]->save();

        $this->recommendations[1]->active = false;
        $this->recommendations[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseRecommendationsQuery::NAME)
                ->args(
                    [
                        'active' => false,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseRecommendationsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->recommendations[1]->id,
                                ],
                                [
                                    'id' => $this->recommendations[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseRecommendationsQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $this->loginAsUserWithRole();
        $this->recommendations[0]->active = false;
        $this->recommendations[0]->save();

        $this->recommendations[1]->active = false;
        $this->recommendations[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseRecommendationsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount(13, 'data.' . BaseRecommendationsQuery::NAME . '.data');
    }
}
