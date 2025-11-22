<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Search;

use App\GraphQL\Queries\FrontOffice\Catalog\Search\UnitsSearchQuery;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnitsSearchQueryTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    public const QUERY = UnitsSearchQuery::NAME;

    /** @test */
    public function search_products_by_serial(): void
    {
        $this->loginAsUserWithRole();

        $product_1 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();
        $product_2 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();
        $product_3 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();

        $query = new GraphQLQuery(
            self::QUERY, [
                'serial_numbers' => [
                    $serial_1 = $product_1->serialNumbers->first()->serial_number,
                    $serial_2 = $product_2->serialNumbers->first()->serial_number,
                    $serial_3 = $product_3->serialNumbers->first()->serial_number,
                ]
            ],
            [
                'id',
                'serial_number',
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        [
                            'id' => $product_1->id,
                            'serial_number' => $serial_1
                        ],
                        [
                            'id' => $product_2->id,
                            'serial_number' => $serial_2
                        ],
                        [
                            'id' => $product_3->id,
                            'serial_number' => $serial_3
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(3, 'data.' . self::QUERY)
        ;
    }

    /** @test */
    public function search_and_not_found_product_by_serial(): void
    {
        $this->loginAsUserWithRole();

        $product_1 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();
        $product_2 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();
        $product_3 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();

        $query = new GraphQLQuery(
            self::QUERY, [
            'serial_numbers' => [
                $serial_1 = $product_1->serialNumbers->first()->serial_number,
                $serial_2 = $product_2->serialNumbers->first()->serial_number,
                $serial_3 = $this->faker->postcode,
            ]
        ],
            [
                'id',
                'serial_number',
            ]
        );

        $this->postGraphQL($query->getQuery())
//            ->dump()
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        [
                            'id' => $product_1->id,
                            'serial_number' => $serial_1
                        ],
                        [
                            'id' => $product_2->id,
                            'serial_number' => $serial_2
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.' . self::QUERY)
        ;
    }
}

