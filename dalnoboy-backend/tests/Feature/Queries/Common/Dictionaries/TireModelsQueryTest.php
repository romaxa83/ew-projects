<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireModelsQuery;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireModelsQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $tireModels;

    public function setUp(): void
    {
        parent::setUp();

        $this->tireModels = TireModel::factory()
            ->count(15)
            ->create();
    }

    public function test_get_tire_models_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseTireModelsQuery::NAME)
                ->args(['per_page' => 2000])
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'title',
                            'tire_make' => [
                                'id',
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
                        BaseTireModelsQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'title',
                                    'tire_make' => [
                                            'id',
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                TireModel::query()
                    ->count(),
                'data.' . BaseTireModelsQuery::NAME . '.data'
            );
    }

    public function test_get_tire_models_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_tire_make(): void
    {
        $this->loginAsAdminWithRole();
        $tireMake = TireMake::factory()->create();
        $this->tireModels[0]->tire_make_id = $tireMake->getKey();
        $this->tireModels[0]->save();
        $this->tireModels[1]->tire_make_id = $tireMake->getKey();
        $this->tireModels[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireModelsQuery::NAME)
                ->args(
                    [
                        'tire_make' => $tireMake->getKey(),
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
                        BaseTireModelsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireModels[1]->id,
                                ],
                                [
                                    'id' => $this->tireModels[0]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireModelsQuery::NAME . '.data');
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireModels[0]->active = false;
        $this->tireModels[0]->save();
        $this->tireModels[1]->active = false;
        $this->tireModels[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireModelsQuery::NAME)
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
                        BaseTireModelsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireModels[1]->id,
                                ],
                                [
                                    'id' => $this->tireModels[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireModelsQuery::NAME . '.data');
    }

    public function test_filter_by_moderated(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireModels[0]->is_moderated = false;
        $this->tireModels[0]->save();
        $this->tireModels[1]->is_moderated = false;
        $this->tireModels[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireModelsQuery::NAME)
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
                        BaseTireModelsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireModels[1]->id,
                                ],
                                [
                                    'id' => $this->tireModels[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireModelsQuery::NAME . '.data');
    }

    public function test_search(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireModels[0]->title = 'ttest title';
        $this->tireModels[0]->save();
        $this->tireModels[1]->title = 'ttest title2';
        $this->tireModels[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireModelsQuery::NAME)
                ->args(
                    [
                        'query' => 'ttest',
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                            'created_at',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseTireModelsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireModels[1]->id,
                                ],
                                [
                                    'id' => $this->tireModels[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireModelsQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $startCount = TireModel::query()
            ->active()
            ->count();

        $this->loginAsUserWithRole();
        $this->tireModels[0]->active = false;
        $this->tireModels[0]->save();
        $this->tireModels[1]->active = false;
        $this->tireModels[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseTireModelsQuery::NAME)
                ->args(['per_page' => 2000])
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
            ->assertJsonCount($startCount - 2, 'data.' . BaseTireModelsQuery::NAME . '.data');
    }
}
