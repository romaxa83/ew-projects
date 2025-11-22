<?php

namespace Tests\Feature\Queries\BackOffice\Security;

use App\GraphQL\Queries\BackOffice\Security\IpAccessQuery;
use App\Models\Admins\Admin;
use App\Permissions\Security\IpAccessListPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class BaseIpAccessQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const QUERY = IpAccessQuery::NAME;

    protected function loginAsIpAccessManager(): Admin
    {
        return $this->loginAsAdmin()->assignRole(
            $this->generateRole('Ip access manager', [IpAccessListPermission::KEY], Admin::GUARD)
        );
    }
}
