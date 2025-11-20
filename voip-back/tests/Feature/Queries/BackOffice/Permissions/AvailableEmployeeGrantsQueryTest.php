<?php

namespace Tests\Feature\Queries\BackOffice\Permissions;

use App\GraphQL\Queries\BackOffice\Permissions\AvailableEmployeeGrantsQuery;
use App\Models\Employees\Employee;
use Tests\TestCase;

class AvailableEmployeeGrantsQueryTest extends TestCase
{
    public const QUERY = AvailableEmployeeGrantsQuery::NAME;

    public function test_cant_get_available_user_permissions_for_employee(): void
    {
        $this->loginAsEmployee();

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
        $result = $this->postGraphQLBackOffice(['query' => $query]);

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

       $this->postGraphQLBackOffice(['query' => $query])
            ->assertJsonCount(
                count(config('grants.matrix.' . Employee::GUARD . '.groups')),
                'data.' . self::QUERY
            )
        ;
    }
}
