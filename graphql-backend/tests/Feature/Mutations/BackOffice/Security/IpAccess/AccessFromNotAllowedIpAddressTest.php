<?php

namespace Tests\Feature\Mutations\BackOffice\Security\IpAccess;

use App\GraphQL\Queries\BackOffice\Admins\AdminsQuery;
use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class AccessFromNotAllowedIpAddressTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public const QUERY = AdminsQuery::NAME;

    public function test_logged_user_has_unauthorized_error_if_his_ip_is_not_allowed(): void
    {
        $ipAddress = '192.168.1.198';
        Config::set('security.ip-access.list', [$ipAddress]);
        Config::set('security.ip-access.enabled', true);

        $this->loginAsAdminManager();

        $query = sprintf('query { %s { data { id } } }', self::QUERY);

        $response = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $this->assertGraphQlUnauthorized($response);
    }

    public function test_logged_user_with_allowed_ip(): void
    {
        $admin = Admin::factory()->create();
        $ipAddress = '192.168.1.198';
        Config::set('security.ip-access.list', [$ipAddress]);
        Config::set('security.ip-access.enabled', true);

        $this->loginAsAdminManager();

        $query = sprintf('query { %s { data { id } } }', self::QUERY);

        $this->serverVariables['REMOTE_ADDR'] = $ipAddress;

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $admin->id
                                ]
                            ]
                        ],
                    ],
                ]
            );
    }

}
