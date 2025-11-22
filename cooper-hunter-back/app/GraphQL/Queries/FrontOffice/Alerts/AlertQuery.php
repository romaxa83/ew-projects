<?php


namespace App\GraphQL\Queries\FrontOffice\Alerts;


use App\GraphQL\Queries\Common\Alerts\BaseAlertQuery;
use App\GraphQL\Types\Alerts\AlertMemberType;
use GraphQL\Type\Definition\Type;

class AlertQuery extends BaseAlertQuery
{

    public function type(): Type
    {
        return AlertMemberType::paginate();
    }

    protected function setQueryGuard(): void
    {
        $this->setMemberGuard();
    }
}
