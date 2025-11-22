<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Products\Keywords;

use App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords\ProductKeywordUpdateMutation;
use App\Models\Catalog\Products\ProductKeyword;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductKeywordUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ProductKeywordUpdateMutation::NAME;

    public function test_create(): void
    {
        $this->loginAsSuperAdmin();

        $keyword = ProductKeyword::factory()->create();

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'id' => $keyword->id,
                    'input' => [
                        'product_id' => $keyword->product_id,
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