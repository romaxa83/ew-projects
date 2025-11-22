<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireTypesQuery;
use App\Models\Dictionaries\TireType;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireTypesQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $tireTypes;

    public function setUp(): void
    {
        parent::setUp();

        $this->tireTypes = TireType::factory()
            ->count(15)
            ->create();
    }

    public function test_get_tire_types_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseTireTypesQuery::NAME)
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
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseTireTypesQuery::NAME => [
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
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                TireType::query()
                    ->count(),
                'data.' . BaseTireTypesQuery::NAME . '.data'
            );
    }

    public function test_get_tire_types_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireTypes[0]->active = false;
        $this->tireTypes[0]->save();
        $this->tireTypes[1]->active = false;
        $this->tireTypes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireTypesQuery::NAME)
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
                        BaseTireTypesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireTypes[1]->id,
                                ],
                                [
                                    'id' => $this->tireTypes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireTypesQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $this->loginAsUserWithRole();
        $this->tireTypes[0]->active = false;
        $this->tireTypes[0]->save();
        $this->tireTypes[1]->active = false;
        $this->tireTypes[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseTireTypesQuery::NAME)
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
            ->assertJsonCount(13, 'data.' . BaseTireTypesQuery::NAME . '.data');
    }
}
