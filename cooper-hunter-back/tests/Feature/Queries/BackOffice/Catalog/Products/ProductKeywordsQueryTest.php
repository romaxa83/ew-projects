<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Products;

use App\GraphQL\Queries\BackOffice\Catalog\Products\ProductKeywordsQuery;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductKeyword;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductKeywordsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = ProductKeywordsQuery::NAME;

    public function test_get_keywords_list(): void
    {
        $this->loginAsSuperAdmin();

        $product = Product::factory()
            ->has(
                ProductKeyword::factory()
                    ->count(5),
                'keywords'
            )
            ->create();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'product_id' => $product->id,
                ]
            )
            ->select(
                [
                    'id',
                    'keyword',
                ]
            )->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(5, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                                'keyword',
                            ]
                        ],
                    ],
                ]
            );
    }
}