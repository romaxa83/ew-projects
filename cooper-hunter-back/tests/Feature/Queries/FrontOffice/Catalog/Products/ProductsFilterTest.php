<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Products;

use App\GraphQL\Queries\FrontOffice\Catalog\Products\ProductsQuery;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Products\Product;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductsFilterTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = ProductsQuery::NAME;

    public function test_filter_by_AND_condition(): void
    {
        $v1 = Value::factory()->create();
        $v2 = Value::factory()->create();
        $v3 = Value::factory()->create();
        $v4 = Value::factory()->create();

        $p1 = Product::factory()
            ->hasAttached($v1)
            ->hasAttached($v2)
            ->create();

        $p2 = Product::factory()
            ->hasAttached($v1)
            ->hasAttached($v3)
            ->hasAttached($v4)
            ->create();

        $this->assertFilterCorrect(
            $this->getQuery([$v1->id, $v2->id]),
            [
                [
                    'id' => $p1->id
                ]
            ],
            1
        );

        $this->assertFilterCorrect(
            $this->getQuery([$v1->id, $v3->id]),
            [
                [
                    'id' => $p2->id
                ]
            ],
            1
        );

        $this->assertFilterCorrect(
            $this->getQuery([$v1->id]),
            [
                [
                    'id' => $p1->id
                ],
                [
                    'id' => $p2->id
                ]
            ],
            2
        );

        $this->assertFilterCorrect(
            $this->getQuery([$v1->id, $v3->id, $v4->id]),
            [
                [
                    'id' => $p2->id
                ]
            ],
            1
        );
    }

    protected function assertFilterCorrect(array $query, array $data, int $count): void
    {
        $this->postGraphQL($query)
            ->assertJsonCount($count, 'data.' . self::QUERY . '.data')
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => $data,
                        ],
                    ],
                ]
            );
    }

    protected function getQuery(array $valueIds): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'sort' => 'sort-asc',
                    'value_ids' => $valueIds,
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
    }

    public function test_filter_with_AND_condition_in_same_feature(): void
    {
        $f1 = Feature::factory()->create();

        $v1 = Value::factory()
            ->for($f1)
            ->create();

        $v1_1 = Value::factory()
            ->for($f1)
            ->create();

        $v2 = Value::factory()->create();

        $p1 = Product::factory()
            ->hasAttached($v1)
            ->hasAttached($v2)
            ->create();

        $p2 = Product::factory()
            ->hasAttached($v1_1)
            ->hasAttached($v2)
            ->create();

        $this->assertFilterCorrect(
            $this->getQuery([$v1->id, $v1_1->id, $v2->id]),
            [
                [
                    'id' => $p1->id
                ],
                [
                    'id' => $p2->id
                ]
            ],
            2
        );
    }
}
