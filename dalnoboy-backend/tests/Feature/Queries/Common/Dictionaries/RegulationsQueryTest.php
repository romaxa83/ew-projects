<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseRegulationsQuery;
use App\Models\Dictionaries\Regulation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegulationsQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $regulations;

    public function setUp(): void
    {
        parent::setUp();

        $this->regulations = Regulation::factory()
            ->count(15)
            ->create();
    }

    public function test_get_regulations_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseRegulationsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'days',
                            'distance',
                            'active',
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
                        BaseRegulationsQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'days',
                                    'distance',
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
                Regulation::query()
                    ->count(),
                'data.' . BaseRegulationsQuery::NAME . '.data'
            );
    }

    public function test_get_regulations_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->regulations[0]->active = false;
        $this->regulations[0]->save();

        $this->regulations[1]->active = false;
        $this->regulations[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseRegulationsQuery::NAME)
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
                        BaseRegulationsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->regulations[1]->id,
                                ],
                                [
                                    'id' => $this->regulations[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseRegulationsQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $this->loginAsUserWithRole();
        $this->regulations[0]->active = false;
        $this->regulations[0]->save();

        $this->regulations[1]->active = false;
        $this->regulations[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseRegulationsQuery::NAME)
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
            ->assertJsonCount(13, 'data.' . BaseRegulationsQuery::NAME . '.data');
    }
}
