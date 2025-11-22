<?php

declare(strict_types=1);

namespace Tests\Feature\Queries\BackOffice\News;

use App\GraphQL\Queries\BackOffice\News\TagsQuery;
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
        $this->loginAsSuperAdmin();

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

        return $this->postGraphQLBackOffice($query->getQuery());
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $this->assertServerError($this->query(), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $this->assertServerError($this->query(), 'Unauthorized');
    }
}
