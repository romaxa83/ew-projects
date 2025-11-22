<?php


namespace App\GraphQL\Mutations\BackOffice\Alerts;


use App\GraphQL\Mutations\Common\Alerts\BaseAlertSetReadMutation;

class AlertSetReadMutation extends BaseAlertSetReadMutation
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
