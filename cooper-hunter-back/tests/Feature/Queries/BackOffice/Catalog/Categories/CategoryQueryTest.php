<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Categories;

use App\GraphQL\Queries\BackOffice\Catalog\Categories\CategoryQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\Catalog\Products\Product;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoryQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CategoryQuery::NAME;

    public function test_get_category_with_parent(): void
    {
        $this->loginAsSuperAdmin();

        $category = Category::factory()
            ->has(
                CategoryTranslation::factory()->allLocales(),
                'translations'
            )
            ->for(
                Category::factory()
                    ->has(
                        CategoryTranslation::factory()->allLocales(),
                        'translations'
                    ),
                'parent'
            )->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'id' => $category->id
            ],
            [
                'id',
                'active',
                'products_count',
                'parent' => [
                    'id',
                    'translations' => [
                        'title',
                        'description',
                        'language',
                    ],
                ],
                'translations' => [
                    'title',
                    'description',
                    'language',
                ],
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOK()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'id',
                            'active',
                            'parent' => [
                                'id',
                                'translations' => [
                                    [
                                        'title',
                                        'description',
                                        'language',
                                    ]
                                ],
                            ],
                            'translations' => [
                                [
                                    'title',
                                    'description',
                                    'language',
                                ]
                            ],
                        ],
                    ],
                ]
            );
    }

    public function test_get_category_seer(): void
    {
        $this->loginAsSuperAdmin();

        $category = Category::factory()
            ->has(
                CategoryTranslation::factory()
                    ->allLocales(),
                'translations'
            )
            ->has(
                Category::factory()
                    ->has(
                        CategoryTranslation::factory()
                            ->allLocales(),
                        'translations'
                    )
                    ->has(
                        Product::factory(['seer' => 20])
                    )
                    ->has(
                        Product::factory(['seer' => 40])
                    ),
                'children'
            )
            ->enableSeer()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(self::QUERY)
                ->args(
                    [
                        'id' => $category->id
                    ]
                )
                ->select(
                    [
                        'seer'
                    ]
                )
                ->make()
        )
            ->assertOK()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'seer' => 40
                        ],
                    ],
                ]
            );
    }
}
