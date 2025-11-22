<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireHeightsQuery;
use App\Models\Dictionaries\TireHeight;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireHeightsQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $tireHeights;

    public function setUp(): void
    {
        parent::setUp();

        $this->tireHeights = TireHeight::factory()
            ->count(15)
            ->create();
    }

    public function test_get_tire_heights_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseTireHeightsQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                            'value',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseTireHeightsQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'value',
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                TireHeight::query()
                    ->count(),
                'data.' . BaseTireHeightsQuery::NAME . '.data'
            );
    }

    public function test_get_tire_heights_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireHeights[0]->active = false;
        $this->tireHeights[0]->value = 20;
        $this->tireHeights[0]->save();
        $this->tireHeights[1]->active = false;
        $this->tireHeights[1]->value = 10;
        $this->tireHeights[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireHeightsQuery::NAME)
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
                        BaseTireHeightsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireHeights[1]->id,
                                ],
                                [
                                    'id' => $this->tireHeights[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireHeightsQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $startCount = TireHeight::query()
            ->active()
            ->count();

        $this->loginAsUserWithRole();
        $this->tireHeights[0]->active = false;
        $this->tireHeights[0]->save();
        $this->tireHeights[1]->active = false;
        $this->tireHeights[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseTireHeightsQuery::NAME)
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
            ->assertJsonCount($startCount - 2, 'data.' . BaseTireHeightsQuery::NAME . '.data');
    }
}
