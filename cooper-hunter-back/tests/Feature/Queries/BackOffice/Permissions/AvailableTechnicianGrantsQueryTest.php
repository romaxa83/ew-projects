<?php

namespace Tests\Feature\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\BackOffice\Permissions\AvailableTechnicianGrantsQuery;
use App\Models\Technicians\Technician;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AvailableTechnicianGrantsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = AvailableTechnicianGrantsQuery::NAME;

    public function test_cant_get_available_user_permissions_for_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_get_available_user_permissions_for_not_auth();
    }

    public function test_cant_get_available_user_permissions_for_not_auth(): void
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
        $result = $this->postGraphQLBackOffice(compact('query'));

        $this->assertGraphQlUnauthorized($result);
    }

    public function test_it_get_list_of_user_permissions_groups_for_admin(): void
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
        $result = $this->postGraphQLBackOffice(compact('query'));

        $grants = $result->json('data.' . self::QUERY);

        self::assertCount(
            count(config('grants.matrix.' . Technician::GUARD . '.groups')),
            $grants
        );
    }
}
