<?php

namespace Tests\Feature\Queries\BackOffice\Security;

use App\GraphQL\Queries\BackOffice\Security\IpAccessQuery;
use App\Models\Admins\Admin;
use App\Permissions\Security\IpAccessListPermission;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

abstract class BaseIpAccessQueryTest extends TestCase
{
    use RoleHelperHelperTrait;

    public const QUERY = IpAccessQuery::NAME;

    protected function loginAsIpAccessManager(): Admin
    {
        return $this->loginAsAdmin()->assignRole(
            $this->generateRole('Ip access manager', [IpAccessListPermission::KEY], Admin::GUARD)
        );
    }
}
