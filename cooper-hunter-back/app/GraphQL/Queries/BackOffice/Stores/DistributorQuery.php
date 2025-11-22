<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\BackOffice\Stores;

use App\GraphQL\Queries\Common\Stores\BaseDistributorQuery;
use App\Permissions\Stores\Distributors\DistributorListPermission;

class DistributorQuery extends BaseDistributorQuery
{
    public const PERMISSION = DistributorListPermission::KEY;

    public function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
