<?php

namespace Tests\Feature\Queries\FrontOffice\InnovativeFeatures;

use App\GraphQL\Queries\FrontOffice\About\SpecificationsQuery;
use App\Models\Catalog\Features\Specification;
use App\Models\Catalog\Features\SpecificationTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InnovativeFeaturesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = SpecificationsQuery::NAME;

    public function test_features_list(): void
    {
        Specification::factory()
            ->times(10)
            ->has(SpecificationTranslation::factory()->allLocales(), 'translations')
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            select: [
                'id',
                'icon',
                'translation' => [
                    'title',
                    'description',
                ],
            ],
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonCount(10, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                                'icon',
                                'translation' => [
                                    'title',
                                    'description',
                                ],
                            ]
                        ],
                    ],
                ]
            );
    }
}
