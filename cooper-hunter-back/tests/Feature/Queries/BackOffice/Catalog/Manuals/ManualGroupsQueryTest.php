<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Manuals;

use App\GraphQL\Queries\BackOffice\Catalog\Manuals\ManualGroupsQuery;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Manuals\ManualGroupTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManualGroupsQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const QUERY = ManualGroupsQuery::NAME;

    public function test_manual_groups_list(): void
    {
        $this->loginAsSuperAdmin();

        ManualGroup::factory()
            ->times(10)
            ->has(
                ManualGroupTranslation::factory()
                    ->locale(),
                'translations'
            )
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'per_page' => 5
            ],
            [
                'data' => [
                    'id'
                ],
            ],
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(5, 'data.' . self::QUERY . '.data');
    }

    public function test_manual_groups_filter(): void
    {
        $this->loginAsSuperAdmin();

        $groups = ManualGroup::factory()
            ->times(10)
            ->has(
                ManualGroupTranslation::factory()
                    ->locale(),
                'translations'
            )
            ->create();

        $translation = $groups[0]->translations()
            ->first();

        $translation->title = $this->faker->lexify;
        $translation->save();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'query' => $translation->title,
                ]
            )
            ->select(
                [
                    'data' => [
                        'id'
                    ]
                ]
            );

        $this->postGraphQLBackOffice($query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $groups[0]->id
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data');
    }
}
