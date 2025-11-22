<?php

namespace Tests\Feature\Queries\FrontOffice\Home;

use App\GraphQL\Queries\FrontOffice\HomePage\HomeCategoriesQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HomeCategoriesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = HomeCategoriesQuery::NAME;

    public function test_homepage_categories(): void
    {
        Category::factory()->times(6)
            ->has(Product::factory())
            ->create(
                [
                    'main' => true,
                ]
            );

        Category::factory()->times(6)->create();

        $query = new GraphQLQuery(
            self::QUERY,
            select: [
                'id'
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonCount(6, 'data.' . self::QUERY);
    }
}
