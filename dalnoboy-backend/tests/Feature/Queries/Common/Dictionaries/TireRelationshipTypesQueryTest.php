<?php

namespace Tests\Feature\Queries\Common\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireRelationshipTypesQuery;
use App\Models\Dictionaries\TireRelationshipType;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TireRelationshipTypesQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $tireRelationshipTypes;

    public function setUp(): void
    {
        parent::setUp();

        $this->tireRelationshipTypes = TireRelationshipType::factory()
            ->count(15)
            ->create();
    }

    public function test_get_tire_relationship_types_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseTireRelationshipTypesQuery::NAME)
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
                        BaseTireRelationshipTypesQuery::NAME => [
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
                TireRelationshipType::query()
                    ->count(),
                'data.' . BaseTireRelationshipTypesQuery::NAME . '.data'
            );
    }

    public function test_get_tire_relationship_types_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->tireRelationshipTypes[0]->active = false;
        $this->tireRelationshipTypes[0]->save();
        $this->tireRelationshipTypes[1]->active = false;
        $this->tireRelationshipTypes[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTireRelationshipTypesQuery::NAME)
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
                        BaseTireRelationshipTypesQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tireRelationshipTypes[1]->id,
                                ],
                                [
                                    'id' => $this->tireRelationshipTypes[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTireRelationshipTypesQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $this->loginAsUserWithRole();
        $this->tireRelationshipTypes[0]->active = false;
        $this->tireRelationshipTypes[0]->save();
        $this->tireRelationshipTypes[1]->active = false;
        $this->tireRelationshipTypes[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseTireRelationshipTypesQuery::NAME)
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
            ->assertJsonCount(13, 'data.' . BaseTireRelationshipTypesQuery::NAME . '.data');
    }
}
