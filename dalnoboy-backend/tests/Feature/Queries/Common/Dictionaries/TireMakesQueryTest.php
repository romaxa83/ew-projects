<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireMakesQuery;
use App\Models\Dictionaries\TireMake;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireMakesQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $tireMakes;

    public function setUp(): void
    {
        parent::setUp();

        $this->tireMakes = TireMake::factory()
            ->count(15)
            ->create();
    }

    public function test_get_tire_makes_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseTireMakesQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'title',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseTireMakesQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'title',
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                TireMake::query()
                    ->count(),
                'data.' . BaseTireMakesQuery::NAME . '.data'
            );
    }

    public function test_get_tire_makes_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireMakes[0]->active = false;
        $this->tireMakes[0]->title = 'B title';
        $this->tireMakes[0]->save();
        $this->tireMakes[1]->active = false;
        $this->tireMakes[1]->title = 'A title';
        $this->tireMakes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireMakesQuery::NAME)
                ->args(
                    [
                        'active' => false,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseTireMakesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireMakes[1]->id,
                                ],
                                [
                                    'id' => $this->tireMakes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireMakesQuery::NAME . '.data');
    }

    public function test_filter_by_moderated(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireMakes[0]->is_moderated = false;
        $this->tireMakes[0]->title = 'B title';
        $this->tireMakes[0]->save();
        $this->tireMakes[1]->is_moderated = false;
        $this->tireMakes[1]->title = 'A title';
        $this->tireMakes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireMakesQuery::NAME)
                ->args(
                    [
                        'is_moderated' => false,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseTireMakesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireMakes[1]->id,
                                ],
                                [
                                    'id' => $this->tireMakes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireMakesQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $startCount = TireMake::query()
            ->active()
            ->count();

        $this->loginAsUserWithRole();
        $this->tireMakes[0]->active = false;
        $this->tireMakes[0]->save();
        $this->tireMakes[1]->active = false;
        $this->tireMakes[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseTireMakesQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount($startCount - 2, 'data.' . BaseTireMakesQuery::NAME . '.data');
    }
}
