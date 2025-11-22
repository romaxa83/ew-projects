<?php

namespace Tests\Feature\Queries\FrontOffice\Sitemaps;

use App\GraphQL\Queries\FrontOffice\Sitemaps\SitemapCategoriesQuery;
use App\Models\Catalog\Categories\Category;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SitemapCategoriesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = SitemapCategoriesQuery::NAME;

    public function test_get_list(): void
    {
        $query = GraphQLQuery::query(self::QUERY)
            ->select(
                [
                    'id',
                    'slug',
                    'updated_at',
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJsonCount(
                Category::query()->where('active', true)->count(),
                'data.' . self::QUERY
            );
    }
}
