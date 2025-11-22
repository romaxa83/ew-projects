<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Manuals;

use App\GraphQL\Queries\FrontOffice\Catalog\Manuals\ManualCategoryQuery;
use App\Models\Catalog\Products\Product;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;

class ManualCategoryQueryTest extends ManualCategoriesQueryTest
{
    public const QUERY = ManualCategoryQuery::NAME;

    public function test_get_categorized_product_manuals(): void
    {
        $category = $this->buildCategoriesWithManuals()->first();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'category_id' => $category->id,
                ]
            )
            ->select(
                [
                    'category_name',
                    'products' => [
                        'id',
                        'title',
                        'slug',
                    ],
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(2, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'category_name',
                                'products' => [
                                    [
                                        'id',
                                        'title',
                                        'slug',
                                    ]
                                ],
                            ]
                        ],
                    ],
                ]
            );
    }

    public function test_get_categorized_product_manuals_by_name(): void
    {
        $category = $this->buildCategoriesWithManuals()->first();
        $product = Product::query()->where('category_id', $category->children->first()->id)->first();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'category_id' => $category->id,
                    'search' => $product->title
                ]
            )
            ->select(
                [
                    'category_name',
                    'products' => [
                        'id',
                        'title',
                    ],
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'category_name',
                                'products' => [
                                    [
                                        'id',
                                        'title',
                                    ]
                                ],
                            ]
                        ],
                    ],
                ]
            );
    }
}
