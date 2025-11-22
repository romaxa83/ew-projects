<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseProblemsQuery;
use App\Models\Dictionaries\Problem;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProblemsQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $problems;

    public function setUp(): void
    {
        parent::setUp();

        $this->problems = Problem::factory()
            ->count(15)
            ->create();
    }

    public function test_get_problems_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseProblemsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'translate' => [
                                'title',
                                'language'
                            ],
                            'translates' => [
                                'title',
                                'language'
                            ],
                            'recommendations' => [
                                'id'
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
                        BaseProblemsQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'translate' => [
                                        'title',
                                        'language'
                                    ],
                                    'translates' => [
                                        '*' => [
                                            'title',
                                            'language'
                                        ]
                                    ],
                                    'recommendations' => [
                                        '*' => [
                                            'id',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                Problem::query()
                    ->count(),
                'data.' . BaseProblemsQuery::NAME . '.data'
            );
    }

    public function test_get_problems_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->problems[0]->active = false;
        $this->problems[0]->save();

        $this->problems[1]->active = false;
        $this->problems[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseProblemsQuery::NAME)
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
                        BaseProblemsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->problems[1]->id,
                                ],
                                [
                                    'id' => $this->problems[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseProblemsQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $this->loginAsUserWithRole();
        $this->problems[0]->active = false;
        $this->problems[0]->save();

        $this->problems[1]->active = false;
        $this->problems[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseProblemsQuery::NAME)
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
            ->assertJsonCount(13, 'data.' . BaseProblemsQuery::NAME . '.data');
    }
}
