<?php

namespace App\GraphQL\Queries\FrontOffice\Warranty\WarrantyInfo;

use App\GraphQL\Queries\Common\Warranty\WarrantyInfo\BaseWarrantyInfoQuery;

class WarrantyInfoQuery extends BaseWarrantyInfoQuery
{
    public function __construct()
    {
        $this->setMemberGuard();
    }
}
