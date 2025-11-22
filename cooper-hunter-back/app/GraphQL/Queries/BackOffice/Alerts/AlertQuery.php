<?php


namespace App\GraphQL\Queries\BackOffice\Alerts;


use App\GraphQL\Queries\Common\Alerts\BaseAlertQuery;
use App\GraphQL\Types\Alerts\AlertAdminType;
use GraphQL\Type\Definition\Type;

class AlertQuery extends BaseAlertQuery
{

    public function type(): Type
    {
        return AlertAdminType::paginate();
    }

    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
