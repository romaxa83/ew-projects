<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Search;

use App\GraphQL\Queries\FrontOffice\Catalog\Search\CatalogSearchQuery;
use App\Models\Catalog\Products\Product;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CatalogSearchQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CatalogSearchQuery::NAME;

    public function test_total_count_products(): void
    {
        Product::factory()
            ->times($count = 10)
            ->sequence(
                static fn(Sequence $s) => ['title' => 'Title_' . $s->index],
            )
            ->create();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'per_page' => 5,
                    'query' => 'Title_',
                ]
            )
            ->select(
                [
                    'data' => [
                        'id'
                    ],
                    'meta' => [
                        'search_result'
                    ],
                ]
            )
            ->make();

        $totalResults = trans_choice(
            'messages.products_count',
            $count,
            compact('count')
        );

        $this->postGraphQL($query)
            ->assertJsonCount(5, 'data.' . self::QUERY . '.data')
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'meta' => [
                                'search_result' => $totalResults
                            ],
                        ]
                    ]
                ]
            );
    }

    public function test_inactive_products_not_included_in_search_result(): void
    {
        Product::factory()
            ->state(
                [
                    'title' => 'active'
                ]
            )
            ->create();

        Product::factory()
            ->disabled()
            ->state(
                [
                    'title' => 'active false'
                ]
            )
            ->create();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'query' => 'active'
                ]
            )
            ->select(
                [
                    'data' => [
                        'id'
                    ]
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data');
    }

    public function test_search_by_keywords(): void
    {
        $products = Product::factory()
            ->times(10)
            ->sequence(
                static fn(Sequence $s) => ['title' => 'Title_' . $s->index],
            )
            ->create();

        /** @var Product $p1 */
        $p1 = $products->shift();
        $p1->keywords()->createMany(
            [
                [
                    'keyword' => 'foo',
                ],
                [
                    'keyword' => 'bar',
                ]
            ]
        );

        /** @var Product $p1 */
        $p1 = $products->shift();
        $p1->keywords()->createMany(
            [
                [
                    'keyword' => 'bar',
                ],
                [
                    'keyword' => 'baz',
                ]
            ]
        );

        $this->assertKeywordSearch('foo', 1);
        $this->assertKeywordSearch('bar', 2);
    }

    public function assertKeywordSearch(string $keyword, int $results): void
    {
        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'query' => $keyword,
                ]
            )
            ->select(
                [
                    'data' => [
                        'id'
                    ],
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJsonCount($results, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id'
                                ]
                            ],
                        ]
                    ]
                ]
            );
    }
}
