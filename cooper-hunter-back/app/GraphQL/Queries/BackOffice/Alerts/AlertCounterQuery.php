<?php


namespace App\GraphQL\Queries\BackOffice\Alerts;


use App\GraphQL\Queries\Common\Alerts\BaseAlertCounterQuery;

class AlertCounterQuery extends BaseAlertCounterQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
