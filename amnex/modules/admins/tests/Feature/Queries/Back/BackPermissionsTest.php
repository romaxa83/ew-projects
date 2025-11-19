<?php

namespace Wezom\Admins\Tests\Feature\Queries\Back;

use Illuminate\Testing\TestResponse;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class BackPermissionsTest extends TestCase
{
    public function testUnauthorizedCantQuery(): void
    {
        $result = $this->queryRequest();

        $this->assertGraphQlUnauthorized($result);
    }

    public function testAuthorizedCantQuery(): void
    {
        $this->loginAsAdminWithPermissions();

        $result = $this->queryRequest();

        $this->assertGraphQlForbidden($result);
    }

    public function testSuperAdminCanQuery(): void
    {
        $this->loginAsSuperAdmin();

        $result = $this->queryRequest()->assertNoErrors();

        $this->assertCount(
            4,
            array_intersect(
                [
                    'admins.create',
                    'admins.delete',
                    'admins.update',
                    'admins.view',
                ],
                $result->json('data.backPermissions')
            )
        );
    }

    protected function queryRequest(array $args = [], array $select = []): TestResponse
    {
        return $this->postGraphQL(GraphQLQuery::query('backPermissions')
            ->args($args)
            ->select($select)
            ->make());
    }
}
