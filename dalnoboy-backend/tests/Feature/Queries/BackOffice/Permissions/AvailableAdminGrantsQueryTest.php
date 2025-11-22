<?php

namespace Tests\Feature\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\BackOffice\Permissions\AvailableAdminGrantsQuery;
use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AvailableAdminGrantsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = AvailableAdminGrantsQuery::NAME;

    public function test_cant_get_available_admin_permissions_for_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_get_available_admin_permissions_for_not_auth();
    }

    public function test_cant_get_available_admin_permissions_for_not_auth(): void
    {
        $query = sprintf(
            'query { %s {
                    key
                    name
                    position
                    permissions {
                        key
                        name
                        position
                    }
                } }',
            self::QUERY
        );
        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }

    public function test_it_get_list_of_permissions_groups_for_admin(): void
    {
        $this->loginAsAdmin();

        $query = sprintf(
            'query { %s {
                    key
                    name
                    position
                    permissions {
                        key
                        name
                        position
                    }
                } }',
            self::QUERY
        );
        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $grants = $result->json('data.' . self::QUERY);

        $guard = Admin::GUARD;
        $count = count(config("grants.matrix.$guard.groups"));
        self::assertCount($count, $grants);
    }
}
