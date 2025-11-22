<?php

namespace Tests\Feature\Queries\BackOffice\News;

use App\GraphQL\Queries\BackOffice\News\PhotoAlbumQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PhotoAlbumQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = PhotoAlbumQuery::NAME;

    public function test_list(): void
    {
        $this->loginAsSuperAdmin();

        $query = new GraphQLQuery(
            self::QUERY,
            select: [
                'data' => [
                    'id',
                    'name',
                ],
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [],
                        ],
                    ],
                ]
            );
    }
}
