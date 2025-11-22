<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Categories;

use App\GraphQL\Queries\BackOffice\Catalog\Categories\CategoriesForSelectQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoriesForSelectQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CategoriesForSelectQuery::NAME;

    public function test_categories_for_select(): void
    {
        Category::factory()
            ->times(5)
            ->has(CategoryTranslation::factory()->allLocales(), 'translations')
            ->create();

        $this->loginAsSuperAdmin();

        $query = new GraphQLQuery(
            self::QUERY,
            select: [
                'id',
                'disabled',
                'name',
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(Category::query()->cooper()->count(), 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                                'disabled',
                                'name',
                            ]
                        ],
                    ],
                ],
            );
    }

    /** @test */
    public function categories_for_select_with_olmo(): void
    {
        Category::factory()
            ->times(5)
            ->has(CategoryTranslation::factory()->allLocales(), 'translations')
            ->create();

        $this->loginAsSuperAdmin();

        $query = new GraphQLQuery(
            self::QUERY,
            args: [
                'with_olmo' => true
            ],
            select: [
                'id',
                'disabled',
                'name',
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(Category::query()->count(), 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                                'disabled',
                                'name',
                            ]
                        ],
                    ],
                ],
            );
    }
}
