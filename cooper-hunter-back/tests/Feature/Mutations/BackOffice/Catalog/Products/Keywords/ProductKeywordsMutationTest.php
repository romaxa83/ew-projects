<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Products\Keywords;

use App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords\ProductKeywordsModifyMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductKeyword;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductKeywordsMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ProductKeywordsModifyMutation::NAME;

    public function test_modify_keywords(): void
    {
        $this->loginAsSuperAdmin();

        $product = Product::factory()
            ->has(
                ProductKeyword::factory()
                    ->state(['keyword' => $keywordToRemove = 'keyword_to_remove']),
                'keywords'
            )
            ->create();

        $this->assertDatabaseCount(ProductKeyword::TABLE, 1);
        $this->assertDatabaseHas(
            ProductKeyword::TABLE,
            [
                'keyword' => $keywordToRemove
            ]
        );

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'product_id' => $product->id,
                    'keywords' => [$k1 = 'keyword_one', $k2 = 'keyword_two']
                ]
            )
            ->select(
                [
                    'id',
                    'keywords' => [
                        'keyword'
                    ],
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk();

        $this->assertDatabaseCount(ProductKeyword::TABLE, 2);
        $this->assertDatabaseHas(
            ProductKeyword::TABLE,
            [
                'keyword' => $k1
            ]
        );
        $this->assertDatabaseHas(
            ProductKeyword::TABLE,
            [
                'keyword' => $k2
            ]
        );
        $this->assertDatabaseMissing(
            ProductKeyword::TABLE,
            [
                'keyword' => $keywordToRemove
            ]
        );
    }

    public function test_delete_all(): void
    {
        $this->loginAsSuperAdmin();

        $product = Product::factory()
            ->has(
                ProductKeyword::factory()
                    ->times(5),
                'keywords'
            )
            ->create();

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'product_id' => $product->id,
                ]
            )
            ->select(
                [
                    'id',
                    'keywords' => [
                        'keyword'
                    ],
                ]
            )
            ->make();

        $this->assertDatabaseCount(ProductKeyword::TABLE, 5);

        $this->postGraphQLBackOffice($query)
            ->assertOk();

        $this->assertDatabaseCount(ProductKeyword::TABLE, 0);
    }
}