<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireSizesQuery;
use App\Models\Dictionaries\TireSize;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireSizesQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $tireSizes;

    public function setUp(): void
    {
        parent::setUp();

        $this->tireSizes = TireSize::factory()
            ->count(15)
            ->create();
    }

    public function test_get_tire_sizes_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseTireSizesQuery::NAME)
                ->args(['per_page' => 1000])
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'tire_height' => ['id'],
                            'tire_diameter' => ['id'],
                            'tire_width' => ['id'],
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseTireSizesQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'tire_height' => ['id'],
                                    'tire_diameter' => ['id'],
                                    'tire_width' => ['id'],
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                TireSize::query()
                    ->count(),
                'data.' . BaseTireSizesQuery::NAME . '.data'
            );
    }

    public function test_get_tire_sizes_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireSizes[0]->active = false;
        $this->tireSizes[0]->save();
        $this->tireSizes[1]->active = false;
        $this->tireSizes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireSizesQuery::NAME)
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
                        BaseTireSizesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireSizes[1]->id,
                                ],
                                [
                                    'id' => $this->tireSizes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireSizesQuery::NAME . '.data');
    }

    public function test_filter_by_moderated(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireSizes[0]->is_moderated = false;
        $this->tireSizes[0]->save();
        $this->tireSizes[1]->is_moderated = false;
        $this->tireSizes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireSizesQuery::NAME)
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
                        BaseTireSizesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireSizes[1]->id,
                                ],
                                [
                                    'id' => $this->tireSizes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireSizesQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $startCount = TireSize::query()
            ->active()
            ->count();

        $this->loginAsUserWithRole();
        $this->tireSizes[0]->active = false;
        $this->tireSizes[0]->save();
        $this->tireSizes[1]->active = false;
        $this->tireSizes[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseTireSizesQuery::NAME)
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
            ->assertJsonCount($startCount - 2, 'data.' . BaseTireSizesQuery::NAME . '.data');
    }
}
