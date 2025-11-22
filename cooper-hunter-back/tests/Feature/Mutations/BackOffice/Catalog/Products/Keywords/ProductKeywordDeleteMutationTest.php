<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Products\Keywords;

use App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords\ProductKeywordDeleteMutation;
use App\Models\Catalog\Products\ProductKeyword;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductKeywordDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ProductKeywordDeleteMutation::NAME;

    public function test_create(): void
    {
        $this->loginAsSuperAdmin();

        $keyword = ProductKeyword::factory()->create();

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'id' => $keyword->id,
                ]
            )->make();

        $this->postGraphQLBackOffice($query)
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => true,
                    ],
                ]
            );

        $this->assertModelMissing($keyword);
    }
}