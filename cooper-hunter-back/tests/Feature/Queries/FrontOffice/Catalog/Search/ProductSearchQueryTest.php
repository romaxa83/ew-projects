<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Search;

use App\GraphQL\Queries\FrontOffice\Catalog\Search\ProductSearchQuery;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductSearchQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = ProductSearchQuery::NAME;

    public function test_search_product_by_serial(): void
    {
        $this->loginAsUserWithRole();

        $product = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'serial_number' => $serial = $product->serialNumbers->first()->serial_number
            ],
            [
                'id',
                'category' => [
                    'id',
                ],
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $product->id,
                        ],
                    ]
                ]
            );
    }
}
