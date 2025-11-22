<?php

declare(strict_types=1);

namespace Tests\Feature\Queries\FrontOffice\News;

use App\GraphQL\Queries\FrontOffice\News\TagsQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TagQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = TagsQuery::NAME;

    public function test_get_list_success(): void
    {
        $this->query()
            ->assertOk()
            ->assertJsonCount(3, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                            ]
                        ],
                    ],
                ]
            );
    }

    protected function query(array $args = []): TestResponse
    {
        $query = new GraphQLQuery(
            self::QUERY,
            $args,
            [
                'id',
            ]
        );

        return $this->postGraphQL($query->getQuery());
    }
}
