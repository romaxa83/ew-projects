<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Manuals;

use App\GraphQL\Queries\BackOffice\Catalog\Manuals\ManualsQuery;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ManualsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = ManualsQuery::NAME;

    public function test_manuals_list(): void
    {
        $this->loginAsSuperAdmin();

        $manualGroup = ManualGroup::factory()
            ->has(
                Manual::factory()
                    ->count(3)
            )
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'manual_group_id' => $manualGroup->id,
            ],
            [
                'id',
                'pdf' => [
                    'url'
                ],
            ],
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(3, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                                'pdf' => [
                                    'url'
                                ],
                            ]
                        ],
                    ],
                ]
            );
    }

    public function test_manual_by_id(): void
    {
        $this->loginAsSuperAdmin();

        $manualGroup = ManualGroup::factory()
            ->has(
                Manual::factory()
                    ->count(3)
            )
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'manual_group_id' => $manualGroup->id,
                'manual_id' => $manualGroup->manuals->first()->id,
            ],
            [
                'id',
                'pdf' => [
                    'url'
                ],
            ],
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                                'pdf' => [
                                    'url'
                                ],
                            ]
                        ],
                    ],
                ]
            );
    }
}
