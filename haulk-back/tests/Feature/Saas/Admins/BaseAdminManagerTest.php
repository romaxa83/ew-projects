<?php

namespace Tests\Feature\Saas\Admins;

use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

abstract class BaseAdminManagerTest extends TestCase
{
    use DatabaseTransactions;
    use AdminFactory;
    use PermissionFactory;

    protected function requestToAdminListRoute(array $attrs = [], array $headers = []): TestResponse
    {
        return $this->getJson(route('v1.saas.admins.index', $attrs), $headers);
    }

    protected function loginAsAdminManager(): Admin
    {
        $admin = $this->createAdmin()->assignRole(
            $this->createRoleAdminManager()
        );

        $this->loginAsSaasAdmin($admin);

        return $admin;
    }
}
