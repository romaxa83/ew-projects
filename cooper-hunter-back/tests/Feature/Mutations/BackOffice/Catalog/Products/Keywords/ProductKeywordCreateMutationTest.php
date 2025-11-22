<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Products\Keywords;

use App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords\ProductKeywordCreateMutation;
use App\Models\Catalog\Products\Product;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductKeywordCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ProductKeywordCreateMutation::NAME;

    public function test_create(): void
    {
        $this->loginAsSuperAdmin();

        $product = Product::factory()->create();

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'input' => [
                        'product_id' => $product->id,
                        'keyword' => 'hello',
                    ],
                ]
            )
            ->select(
                [
                    'id',
                    'keyword',
                ]
            )->make();

        $this->postGraphQLBackOffice($query)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'keyword',
                        ],
                    ],
                ]
            );
    }
}