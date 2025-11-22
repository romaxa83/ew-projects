<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireSpecificationsQuery;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireSpecification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireSpecificationsQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $tireSpecifications;

    public function setUp(): void
    {
        parent::setUp();

        $this->tireSpecifications = TireSpecification::factory()
            ->count(15)
            ->create();
    }

    public function test_get_tire_specifications_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseTireSpecificationsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'make' => [
                                'id',
                            ],
                            'model' => [
                                'id',
                            ],
                            'type' => [
                                'id',
                            ],
                            'size' => [
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
                        BaseTireSpecificationsQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'make' => [
                                        'id',
                                    ],
                                    'model' => [
                                        'id',
                                    ],
                                    'type' => [
                                        'id',
                                    ],
                                    'size' => [
                                        'id',
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                TireSpecification::query()
                    ->count(),
                'data.' . BaseTireSpecificationsQuery::NAME . '.data'
            );
    }

    public function test_get_tire_specifications_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter(): void
    {
        $this->loginAsAdminWithRole();
        $tireMake = TireMake::factory()->create();
        $tireModel = TireModel::factory()->create();
        $tireSize = TireSize::factory()->create();
        $this->tireSpecifications[0]->make_id = $tireMake->getKey();
        $this->tireSpecifications[0]->model_id = $tireModel->getKey();
        $this->tireSpecifications[0]->size_id = $tireSize->getKey();
        $this->tireSpecifications[0]->save();
        $this->tireSpecifications[1]->make_id = $tireMake->getKey();
        $this->tireSpecifications[1]->model_id = $tireModel->getKey();
        $this->tireSpecifications[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireSpecificationsQuery::NAME)
                ->args(
                    [
                        'tire_make' => $tireMake->getKey(),
                        'tire_model' => $tireModel->getKey(),
                        'tire_size' => $tireSize->getKey(),
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
                        BaseTireSpecificationsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireSpecifications[0]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseTireSpecificationsQuery::NAME . '.data');
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireSpecifications[0]->active = false;
        $this->tireSpecifications[0]->save();
        $this->tireSpecifications[1]->active = false;
        $this->tireSpecifications[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireSpecificationsQuery::NAME)
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
                        BaseTireSpecificationsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireSpecifications[1]->id,
                                ],
                                [
                                    'id' => $this->tireSpecifications[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireSpecificationsQuery::NAME . '.data');
    }

    public function test_filter_by_moderated(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireSpecifications[0]->is_moderated = false;
        $this->tireSpecifications[0]->save();
        $this->tireSpecifications[1]->is_moderated = false;
        $this->tireSpecifications[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireSpecificationsQuery::NAME)
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
                        BaseTireSpecificationsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireSpecifications[1]->id,
                                ],
                                [
                                    'id' => $this->tireSpecifications[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireSpecificationsQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $this->loginAsUserWithRole();
        $this->tireSpecifications[0]->active = false;
        $this->tireSpecifications[0]->save();
        $this->tireSpecifications[1]->active = false;
        $this->tireSpecifications[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseTireSpecificationsQuery::NAME)
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
            ->assertJsonCount(13, 'data.' . BaseTireSpecificationsQuery::NAME . '.data');
    }
}
