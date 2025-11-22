<?php

namespace App\GraphQL\Queries\BackOffice\About;

use App\GraphQL\Queries\Common\About\BaseForMemberPageQuery;
use App\Permissions\About\ForMemberPages\ForMemberPageUpdatePermission;

class ForMemberPageQuery extends BaseForMemberPageQuery
{
    public const PERMISSION = ForMemberPageUpdatePermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }
}
