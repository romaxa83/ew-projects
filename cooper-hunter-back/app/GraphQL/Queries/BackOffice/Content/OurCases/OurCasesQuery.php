<?php

namespace App\GraphQL\Queries\BackOffice\Content\OurCases;

use App\GraphQL\Queries\Common\Content\OurCase\BaseOurCaseQuery;
use App\Permissions\Content\OurCases\OurCaseListPermission;

class OurCasesQuery extends BaseOurCaseQuery
{
    public const PERMISSION = OurCaseListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }
}
